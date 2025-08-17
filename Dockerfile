# Use an official Python runtime as a parent image
FROM python:3.11-slim

# Set the working directory in the container
WORKDIR /app

# Copy the requirements file into the container at /app
COPY requirements.txt .

# Upgrade pip and then install the packages using the full path to python
# This ensures packages are installed for the correct interpreter
RUN /usr/local/bin/python -m pip install --upgrade pip && \
    /usr/local/bin/python -m pip install --no-cache-dir -r requirements.txt

# Copy the rest of the application's code into the container at /app
COPY . .

# Expose ports for Streamlit and FastAPI
EXPOSE 8501
EXPOSE 8000

# The CMD will be specified in docker-compose.yml

