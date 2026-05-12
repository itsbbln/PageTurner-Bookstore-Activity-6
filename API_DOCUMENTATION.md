# PageTurner Bookstore - API Documentation

## Base URL
All API endpoints are prefixed with:
```
http://127.0.0.1:8000/api
```

---

## Rate Limiting

### Overview
The API implements tiered rate limiting to prevent abuse and ensure fair usage.

### Rate Limits by User Role
| User Role | Per-Minute Limit | Per-Second Limit |
|---|---|---|
| Public (not logged in) | 30 requests | 2 requests |
| Standard User (customer) | 60 requests | 2 requests |
| Premium User | 300 requests | 2 requests |
| Admin | 1000 requests | 2 requests |

### Rate Limit Response Headers
When rate limiting is applied, the following headers are included in the response:
- `X-RateLimit-Limit`: The maximum number of requests allowed per window
- `X-RateLimit-Remaining`: The number of requests remaining in the current window
- `Retry-After`: The number of seconds to wait before retrying

### 429 Too Many Requests Response
When the rate limit is exceeded, the API returns a 429 status code with the following JSON response:
```json
{
  "message": "Too Many Requests",
  "limit": 30,
  "retry_after_seconds": 55
}
```

---

## API Endpoints

### 1. Get Books
Retrieve a paginated list of books.

**Endpoint**: `GET /books`

**Authentication**: Not required (public endpoint)

**Query Parameters**:
| Parameter | Type | Required | Description |
|---|---|---|---|
| `category_id` | integer | No | Filter books by category ID |
| `min_price` | float | No | Filter books with price >= this value |
| `max_price` | float | No | Filter books with price <= this value |
| `per_page` | integer | No | Number of results per page (default: 20) |

**Example Request**:
```http
GET /api/books?category_id=1&min_price=10&max_price=50&per_page=10
```

**Example Response (200 OK)**:
```json
{
  "data": [
    {
      "id": 1,
      "categoryId": 1,
      "title": "Ut quia.",
      "author": "Jess Grant",
      "isbn": "9799100227394",
      "price": "88.85",
      "stockQuantity": 66,
      "description": "Non ut possimus illo explicabo fuga...",
      "coverImage": null,
      "createdAt": "2026-04-20T07:02:00.000000Z",
      "updatedAt": "2026-04-20T07:02:00.000000Z",
      "category": {
        "id": 1,
        "name": "Self-Help",
        "description": "In et et dolor sit quaerat quis...",
        "createdAt": "2026-04-20T07:02:00.000000Z",
        "updatedAt": "2026-04-20T07:02:00.000000Z"
      }
    }
  ],
  "path": "http://127.0.0.1:8000/api/books",
  "per_page": 20,
  "next_cursor": "eyJpZCI6MjAsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0=",
  "prev_cursor": null
}
```

---

### 2. Get Authenticated User
Retrieve information about the currently authenticated user.

**Endpoint**: `GET /user`

**Authentication**: Required (Sanctum token)

**Example Response (200 OK)**:
```json
{
  "id": 1,
  "name": "Admin User",
  "email": "adminpageturner@gmail.com",
  "email_verified_at": "2026-04-20T07:02:00.000000Z",
  "role": "admin",
  "created_at": "2026-04-20T07:02:00.000000Z",
  "updated_at": "2026-04-20T07:02:00.000000Z"
}
```

---

## Error Responses

### 400 Bad Request
Returned when the request parameters are invalid.

### 401 Unauthorized
Returned when authentication is required but not provided.

### 403 Forbidden
Returned when the authenticated user doesn't have permission to access the resource.

### 404 Not Found
Returned when the requested resource doesn't exist.

### 422 Unprocessable Entity
Returned when validation fails.

### 429 Too Many Requests
Returned when the rate limit is exceeded (see Rate Limiting section above).

### 500 Internal Server Error
Returned when an unexpected server error occurs.

---

## Usage Examples

### Example 1: Get All Books (Public)
```bash
curl http://127.0.0.1:8000/api/books
```

### Example 2: Get Books in a Specific Category
```bash
curl http://127.0.0.1:8000/api/books?category_id=1
```

### Example 3: Get Books in a Price Range
```bash
curl "http://127.0.0.1:8000/api/books?min_price=10&max_price=100"
```
