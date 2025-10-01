# Use official PHP + Apache image
FROM php:8.1-apache

# Install Python
RUN apt-get update && apt-get install -y python3 python3-pip

# Copy project files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# (Optional) If you have Python dependencies
# COPY requirements.txt .
# RUN pip3 install -r requirements.txt

# Expose port 80
EXPOSE 80
