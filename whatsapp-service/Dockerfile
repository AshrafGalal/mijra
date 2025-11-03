# Use the latest Alpine-based Node image
FROM node:22-alpine

# Install Chromium and necessary dependencies for Puppeteer / WhatsApp Web.js
RUN apk add --no-cache \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ca-certificates \
    ttf-freefont \
    bash

# Set Puppeteer environment variables to use system Chromium
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser \
    NODE_ENV=development \
    PORT=4000

# Set working directory
WORKDIR /var/www/html/whatsapp-service

# Copy package.json and install dependencies
COPY package*.json ./
RUN npm install -g nodemon && npm install

# Copy the rest of your code
COPY . .

# Expose your Node app port
EXPOSE 4000

# Run app with nodemon (auto reload)
CMD ["nodemon", "index.js"]
