FROM python:3.11-slim-bullseye

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    STANZA_RESOURCES_DIR=/stanza_resources

WORKDIR /app

COPY requirements.txt /app/requirements.txt
RUN pip install --no-cache-dir -r requirements.txt

# Pre-create stanza resources directory and (optionally) pre-download Indonesian models
# Note: This step requires network during image build. Comment it out if your build infra blocks outbound network.
RUN mkdir -p ${STANZA_RESOURCES_DIR} \
    && python -c "import stanza; stanza.download('id', model_dir='${STANZA_RESOURCES_DIR}')" || true

COPY . /app

EXPOSE 5000

ENV FLASK_APP=run.py

CMD ["python", "run.py"]
