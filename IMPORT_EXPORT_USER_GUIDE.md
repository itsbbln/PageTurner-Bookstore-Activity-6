# PageTurner Bookstore - Import/Export User Guide

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Accessing Data Management](#accessing-data-management)
3. [Importing Books](#importing-books)
4. [Exporting Books](#exporting-books)
5. [Troubleshooting](#troubleshooting)

---

## Prerequisites
- You must be logged in as an **Admin** user
- URL: http://127.0.0.1:8000
- Admin Credentials:
  - Email: `adminpageturner@gmail.com`
  - Password: `Admin123!`

---

## Accessing Data Management
1. Log in as Admin
2. From the Admin Dashboard, click on **Data Management**
3. Or go directly to: http://127.0.0.1:8000/admin/data-management
4. Click on **Book Data Management**

---

## Importing Books

### Step 1: Download Import Template
1. On the Book Data Management page, click **Download Import Template**
2. Save the CSV file to your computer
3. The template has the following columns:
   - ISBN
   - Title
   - Author
   - Price
   - Stock
   - Category
   - Description

### Step 2: Prepare Your Data
1. Open the template in Excel, Google Sheets, or a text editor
2. Fill in your book data:
   - **ISBN**: Must be valid ISBN-10 or ISBN-13
   - **Title**: Required, max 255 characters
   - **Author**: Required, max 255 characters
   - **Price**: Required, numeric, between 0.01 and 9999.99
   - **Stock**: Required, integer, 0 or higher
   - **Category**: Must match an existing category name exactly
   - **Description**: Optional, text

3. Save the file as CSV or XLSX

### Step 3: Upload and Import
1. On the Book Data Management page:
   - Click **Choose File** and select your CSV/XLSX file
   - (Optional) Check **Update existing books** if you want to update existing records by ISBN
   - (Optional) Check **Allow duplicate file upload** if you've uploaded this file before
2. Click **Import Books**
3. You'll be redirected to the Import Log page
4. Refresh the page periodically to check progress

### Step 4: Check Import Results
- **Status**: "queued" → "running" → "completed" or "completed_with_errors"
- **Successful Rows**: Number of books imported successfully
- **Failed Rows**: Number of books that failed validation
- **Download Failure Report**: If there are failures, click to download a detailed report

---

## Exporting Books

### Step 1: Configure Export Options
On the Book Data Management page, you can configure:
1. **Format**: Choose XLSX, CSV, or PDF
2. **Filters** (optional):
   - Category: Filter by book category
   - Price Range: Filter by minimum and/or maximum price
   - Stock Status: Filter by "In Stock", "Out of Stock", or "Low Stock"
   - Date Range: Filter by creation date
3. **Columns**: Choose which columns to include in the export

### Step 2: Start Export
1. Click **Export Books**
2. Two possibilities:
   - **Small export (< 10,000 records)**: File downloads immediately
   - **Large export (>= 10,000 records)**: Redirected to Export Log page

### Step 3: Check Export Results
- **Status**: "queued" → "running" → "completed"
- **Record Count**: Number of books exported
- **Download**: Click to download the export file when ready

---

## Troubleshooting

### Common Import Issues

#### Issue: "This file already exists"
**Solution**: Check the "Allow duplicate file upload" box before importing.

#### Issue: Many failed rows
**Possible Causes**:
- Invalid ISBN format
- Category doesn't exist (must match exactly)
- Duplicate ISBN (if not updating existing)
- Price or stock out of range

**Solution**: Download the Failure Report to see exactly which rows failed and why.

#### Issue: Import is stuck on "running"
**Solution**:
1. Make sure the queue worker is running:
   ```bash
   php artisan queue:work --queue=imports,exports,default -v
   ```
2. Refresh the import log page

### Common Export Issues

#### Issue: Export is stuck on "running"
**Solution**: Same as import stuck issue - ensure queue worker is running.

#### Issue: Can't download export file
**Solution**:
1. Wait for status to change to "completed"
2. Make sure the file exists in `storage/app/exports/books/`
3. Try refreshing the export log page

### Queue Worker Not Running?
Always keep the queue worker running when doing imports/exports!
```bash
php artisan queue:work --queue=imports,exports,default -v
```

The `-v` flag shows verbose output so you can see what's happening.
