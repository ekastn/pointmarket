.PHONY: help build-cpu build-gpu run-cpu run-gpu size

IMAGE_NAME ?= pointmarket-ai-service
CONTAINER_NAME?= pm-ai-service
PORT ?= 5000

help:
	@echo "Targets:"
	@echo "  build-cpu   - Build CPU-only image ($(IMAGE_NAME):cpu)"
	@echo "  build-gpu   - Build CUDA-enabled image ($(IMAGE_NAME):gpu)"
	@echo "  run-cpu     - Run CPU image on port $(PORT) (uses .env)"
	@echo "  run-gpu     - Run GPU image on port $(PORT) with --gpus all (uses .env)"
	@echo "  size        - Show image sizes filtered by $(IMAGE_NAME)"

build-cpu:
	docker build -f Dockerfile.cpu -t $(IMAGE_NAME):cpu .

build-gpu:
	docker build -f Dockerfile.gpu -t $(IMAGE_NAME):gpu .

run-cpu:
	docker run --rm -p $(PORT):5000 --env-file .env --name $(CONTAINER_NAME)-cpu $(IMAGE_NAME):cpu

run-gpu:
	docker run --rm --gpus all -p $(PORT):5000 --env-file .env $(CONTAINER_NAME)-gpu $(IMAGE_NAME):gpu

size:
	docker images | awk 'NR==1 || $$1 ~ /$(IMAGE_NAME)/'

