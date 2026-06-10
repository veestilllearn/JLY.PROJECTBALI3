# JLY.PROJECTBALI3

Website katalog dan administrasi produk berbasis PHP.

## DevOps Stack

- GitHub Repository
- GitHub Actions
- Docker
- Docker Compose
- PHP 8.2 Apache
- CodeQL
- Trivy
- Gitleaks

## Security

- SAST (CodeQL)
- Dependency Scan (Trivy)
- Secret Detection (Gitleaks)

## Monitoring

- Health Check Endpoint
- Docker Health Check
- Apache Logging

## Menjalankan Project

docker compose up -d

Akses:

http://localhost:8080

Admin Panel:

http://localhost:8080/admin

Health Check:

http://localhost:8080/health.php