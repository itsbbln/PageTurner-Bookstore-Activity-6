# PageTurner Bookstore - Technical Report

## Executive Summary
This technical report documents the architecture, strategies, and implementation details of the PageTurner Bookstore system, focusing on import/export processing, backup strategies, audit logging security, and performance optimization techniques. The system is built on Laravel 10, leveraging modern PHP practices and robust libraries to ensure scalability, reliability, and security.

---

## 1. Architecture Decisions for Chunking and Queuing Strategies

### 1.1 Import Processing Architecture
The import system is designed to handle large datasets (10,000+ records) efficiently while maintaining memory usage below 256MB. This is achieved through three key architectural decisions:

#### 1.1.1 Chunked Reading with Laravel Excel
The `BooksImport` class implements `WithChunkReading` and `WithBatchInserts` interfaces from the Laravel Excel library (Maatwebsite/Excel). This allows the system to:
- Read the CSV/Excel file in chunks of 1000 rows
- Process each chunk independently
- Insert multiple records in a single batch operation
- Keep memory usage constant regardless of dataset size

The chunk size is set to 1000 rows, balancing between database round-trips and memory consumption. Smaller chunk sizes reduce memory usage but increase database operations, while larger sizes may hit memory limits.

#### 1.1.2 Queued Processing with ShouldQueue
The import class implements the `ShouldQueue` interface, which delegates processing to Laravel's queue system instead of running synchronously in the HTTP request. This provides several benefits:
- No HTTP request timeouts (critical for large imports)
- Background processing allows user to continue using the app
- Failed imports can be retried without re-uploading
- Multiple imports can be processed in parallel (if multiple queue workers are running)

Jobs are dispatched to dedicated queues:
- `imports` queue for import jobs
- `exports` queue for export jobs
- `default` queue for other tasks

This separation ensures that import/export jobs don't block other application processes.

#### 1.1.3 Validation and Error Handling
The import system implements comprehensive validation:
- Per-row validation using Laravel's built-in validation rules
- Custom `IsbnRule` for ISBN validation
- Failure tracking via `SkipsOnFailure` interface
- Persistent failure storage in the database
- Failure report generation for download

This architecture ensures that valid records are imported even if some rows fail, providing clear feedback to users about what went wrong.

### 1.2 Export Processing Architecture
The export system follows a similar pattern to imports, with some key differences:

#### 1.2.1 Query-Based Export with Chunking
The `BooksExport` class implements `FromQuery` and `WithChunkReading`, allowing it to:
- Build an Eloquent query with filters
- Fetch records in chunks of 1000 rows
- Stream results to the Excel file without loading all records into memory
- Support large exports (50,000+ records) without timeouts

#### 1.2.2 Conditional Queuing
The system automatically decides whether to queue an export based on record count:
- < 10,000 records: Synchronous export (immediate download)
- >= 10,000 records: Queued export (background processing)

This balances user experience (fast downloads for small datasets) with system stability (no timeouts for large datasets).

---

## 2. Backup Strategy and Disaster Recovery Procedures

### 2.1 Backup Architecture
The system uses the `spatie/laravel-backup` package to provide comprehensive backup functionality.

#### 2.1.1 What Gets Backed Up
- **Database**: Full PostgreSQL dump (all tables and data)
- **Files**: 
  - `storage/app/public` (uploaded book covers and user-generated content)
  - `config` directory (application configuration)
- **Exclusions**:
  - `vendor` directory (Composer dependencies)
  - `node_modules` directory (npm dependencies)
  - Temporary files

#### 2.1.2 Backup Schedule
Backups are automated via Laravel's task scheduler:
- **Full Backup**: Daily at 02:00 AM
- **Backup Cleanup**: Daily at 03:00 AM
- **Retention Policy**:
  - Keep all backups for 7 days
  - Keep daily backups for 7 days
  - Keep weekly backups for 4 weeks
  - Keep monthly backups for 12 months
  - No yearly backups (per lab requirements)

This retention policy ensures that recent backups are available for quick recovery, while older backups are pruned to save storage space.

#### 2.1.3 Storage Configuration
Backups are stored locally on the server filesystem (configurable to use S3, FTP, or other cloud storage). The backup filename includes a timestamp for easy identification.

### 2.2 Disaster Recovery Procedures
#### 2.2.1 Manual Backup Trigger
In case of emergency, a manual backup can be triggered using:
```bash
php artisan backup:trigger --clean
```
This runs a full backup and immediately cleans up old backups according to the retention policy.

#### 2.2.2 Restoration Procedure
To restore from a backup:
1. Download the latest backup ZIP file from `storage/app/Laravel/`
2. Extract the ZIP file
3. Restore the database using the SQL dump file
4. Restore the files to their original locations
5. Verify the application works correctly

#### 2.2.3 Failure Notification
The system is configured to send email notifications on:
- Backup failure
- Backup success (optional)
- Unhealthy backup detection (old backups or large storage usage)
- Cleanup failure

Notifications are sent to the configured backup email address, ensuring that administrators are alerted immediately if something goes wrong.

---

## 3. Security Considerations for Audit Logging

### 3.1 Audit Log Architecture
The system uses `owen-it/laravel-auditing` to provide comprehensive audit logging for all CRUD operations.

#### 3.1.1 What Gets Audited
All models implementing the `Auditable` interface are automatically audited:
- `Book` model (all CRUD operations)
- `User` model (all CRUD operations)
- `Category` model (all CRUD operations)
- `Order` model (all CRUD operations)
- `Review` model (all CRUD operations)

For each audited event, the following information is stored:
- Event type (created, updated, deleted, restored)
- User ID and IP address
- User agent and URL
- Old and new values of changed fields
- Timestamp of the event

#### 3.1.2 Sensitive Data Exclusion
Critical security measure: sensitive data is excluded from audit logs:
- `User` model excludes `password` and `remember_token`
- `Book` model excludes `cover_image` (large binary data not suitable for logs)

This prevents password leaks and other sensitive information from being stored in audit logs, which are accessible to administrators.

#### 3.1.3 Audit Log Access Control
Audit logs are only accessible to admin users:
- Routes are protected by `is_admin` middleware
- Audit log dashboard is at `/admin/audit-logs`
- Audit log details are at `/admin/audit-logs/{audit}`

This ensures that only authorized personnel can view audit trails.

#### 3.1.4 Tamper-Proof Considerations
While the current implementation doesn't use cryptographic checksums for audit logs, the architecture supports future enhancement:
- The `audits` table can be extended to include a hash column
- Each audit entry can be hashed with the previous entry's hash to create a blockchain-like structure
- This would make audit logs tamper-evident

### 3.2 Additional Security Measures
- **Password Hashing**: All user passwords are hashed using bcrypt
- **Two-Factor Authentication**: Available for all users
- **Rate Limiting**: Prevents brute-force attacks on API endpoints
- **Input Validation**: All user input is validated before processing
- **CSRF Protection**: Web forms are protected against cross-site request forgery

---

## 4. Performance Optimization Techniques

### 4.1 Database Optimization
#### 4.1.1 Indexing
Key database columns are indexed to speed up queries:
- Primary keys are automatically indexed
- Foreign keys (like `category_id` on `books` table) are indexed
- Searchable columns can be indexed if needed

#### 4.1.2 Cursor Pagination
API endpoints use cursor pagination instead of offset pagination for large datasets:
- More efficient for large offsets
- Consistent performance regardless of page number
- Reduces database load

#### 4.1.3 Eager Loading
Relationships are eager loaded to prevent N+1 query problems:
- `Book::with('category')` loads all books and their categories in 2 queries instead of N+1

### 4.2 Caching
#### 4.2.1 Query Caching
Frequently accessed data can be cached using Laravel's cache system:
- ETag headers for API responses to reduce bandwidth
- Cache tags for invalidation

#### 4.2.2 HTTP Caching
API responses include ETag headers, allowing clients to cache responses and validate them with `If-None-Match` headers.

### 4.3 Queue Optimization
#### 4.3.1 Dedicated Queues
As mentioned earlier, jobs are separated into dedicated queues:
- `imports` for import jobs
- `exports` for export jobs
- `default` for other tasks

This ensures that long-running import/export jobs don't block time-sensitive tasks.

#### 4.3.2 Multiple Queue Workers
For high-traffic scenarios, multiple queue workers can be run:
```bash
php artisan queue:work --queue=imports,exports,default
```
Each worker processes jobs in parallel, increasing throughput.

### 4.4 Asset Optimization
#### 4.4.1 Vite for Frontend Assets
The system uses Vite for bundling and optimizing frontend assets:
- Fast development server with Hot Module Replacement (HMR)
- Production builds with tree-shaking and minification
- Automatic asset versioning for cache busting

#### 4.4.2 Image Optimization
Uploaded book cover images can be optimized using Laravel's image processing libraries to reduce file sizes.

### 4.5 Memory Management
#### 4.5.1 Chunked Processing
As discussed earlier, both imports and exports use chunked processing to keep memory usage constant:
- Import: 1000-row chunks
- Export: 1000-row chunks
- No matter how large the dataset, memory stays under 256MB

#### 4.5.2 Lazy Loading
Eloquent uses lazy loading by default, which only loads related models when they're accessed, reducing initial memory usage.

---

## Conclusion
The PageTurner Bookstore system implements robust, scalable, and secure architecture for import/export processing, backup management, audit logging, and performance optimization. Key strengths include:

- **Chunked and queued processing** for large datasets
- **Comprehensive backup strategy** with automated scheduling and retention policies
- **Secure audit logging** with sensitive data exclusion and access control
- **Multiple performance optimization techniques** ensuring the system runs efficiently even under load

This architecture provides a solid foundation for future growth and can be extended with additional features as needed.
