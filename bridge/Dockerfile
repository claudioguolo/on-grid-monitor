FROM python:3.12-slim
WORKDIR /app
COPY server.py /app/server.py
ENV PYTHONUNBUFFERED=1 DATA_DIR=/data PORT=8080
RUN mkdir /data && chown -R 10001:10001 /data /app
USER 10001:10001
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=5s CMD python -c "import urllib.request; urllib.request.urlopen('http://127.0.0.1:8080/health', timeout=3)"
CMD ["python", "server.py"]
