FROM golang:latest AS builder

WORKDIR /app

COPY go.mod go.sum ./

RUN go mod download

COPY . .

RUN CGO_ENABLED=0 go build -o main ./cmd/api/

RUN ls -l
RUN pwd

FROM alpine:latest

COPY --from=builder /app/main .

EXPOSE 8080

CMD ["./main"]
