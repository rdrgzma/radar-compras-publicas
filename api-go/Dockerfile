# ==============================================================================
# Dockerfile para radar-api-go
# Build estático multi-stage com Go 1.24+ em Alpine
# ==============================================================================

FROM golang:1.24-alpine AS builder

WORKDIR /app

# Instalar certificados CA e git
RUN apk add --no-cache ca-certificates git

# Cache de dependências
COPY go.mod go.sum ./
RUN go mod download

# Copiar código-fonte
COPY . .

# Compilação estática
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -ldflags="-w -s" -o /app/bin/radar-api ./cmd/api

# Imagem final mínima
FROM alpine:3.21

WORKDIR /app

RUN apk add --no-cache ca-certificates tzdata wget
ENV TZ=America/Sao_Paulo

COPY --from=builder /app/bin/radar-api /app/radar-api

EXPOSE 8000

CMD ["/app/radar-api"]
