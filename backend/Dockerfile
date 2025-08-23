
FROM python:3.12-slim

WORKDIR /app
RUN mkdir -p faiss_index

COPY requirements.txt requirements.txt
RUN pip install --no-cache-dir -r requirements.txt

RUN rm -rf __pycache__
COPY ./backend /app

EXPOSE 8000

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]
