# Document Generator Service

Service for generating PDF documents from DOCX templates using JSON data.

## Requirements

- Docker
- Running conversion service on port 8181

## Quick Start

1. Build the Docker image:
   ```bash
   docker build -t doc-processor .
   ```

2. Run the service:
   ```bash
   docker run -p 8000:8000 doc-processor
   ```

   The service will be available at [http://localhost:8000](http://localhost:8000)

## API Endpoints

OpenAPI - /api/v1/doc

### Upload Document Template

**POST** `/api/v1/documents/upload`

Process a DOCX template with JSON data and returns a PDF.

#### Request

- **Content-Type:** `multipart/form-data`
- **Parameters:**
    - `file`: DOCX template file
    - `json`: JSON data for template processing

#### Example Request Using cURL:

```bash
curl -X POST \
  -F "file=@template.docx" \
  -F "json={\"name\":\"John Doe\",\"age\":30}" \
  http://localhost:8000/api/v1/documents/upload \
  --output result.pdf
```

#### Response

- **Success:** PDF file (`application/pdf`)
- **Error:** JSON with error details

```json
{
    "status": "error",
    "message": "Error description"
}
```

### Health Check

**GET** `/api/v1/health`

Returns service health status.

## Development

### Environment Variables

The service uses the following environment variables:

- `APP_ENV`: Application environment (default: `prod`)
- `CONVERSION_ENDPOINT`: URL of the conversion service (default: `http://localhost:8181/convert`)
- `MESSENGER_TRANSPORT_DSN`: Message transport configuration (default: `sync://`)

### Project Structure

```
├── src/
│   ├── Controller/     # API controllers
│   ├── Service/        # Business logic
│   └── DTO/            # Data Transfer Objects
├── config/            # Application configuration
├── docker/           # Docker configuration files
├── public/           # Web server root
└── var/             # Runtime files
    └── data/        # SQLite database
```

## License

MIT License
