# MongoDB Atlas Migration Guide

This document outlines the steps to set up and migrate to MongoDB Atlas for the maJIStic 2k25 application.

## Prerequisites

1. PHP 7.4 or higher
2. Composer installed
3. MongoDB Atlas account

## Installation

1. Install the MongoDB PHP driver using Composer:

```bash
composer require mongodb/mongodb
```

2. If you're on a Windows environment, you may need to enable the MongoDB extension:
   - Uncomment or add the line `extension=mongodb` in your php.ini file
   - Restart your web server

## Creating MongoDB Atlas Cluster

1. Sign up or log in to [MongoDB Atlas](https://www.mongodb.com/cloud/atlas)
2. Create a new cluster (you can use the free tier)
3. Set up a database user with read/write permissions
4. Configure Network Access to allow connections from your IP address or use `0.0.0.0/0` to allow access from anywhere (not recommended for production)
5. Get your connection string from the Atlas dashboard

## Configuration

1. Update the connection string in `includes/db_config.php` with your MongoDB Atlas connection details:

```php
$connectionString = "mongodb+srv://username:password@your-cluster.mongodb.net/majistic2k25?retryWrites=true&w=majority";
```

2. Replace `username`, `password`, and `your-cluster` with your specific MongoDB Atlas credentials.

## Collection Structure

The application uses two main collections:

1. `registrations` - For student registrations
2. `alumni_registrations` - For alumni registrations

Each document will contain the registration data, with the JIS ID used as a unique identifier within each collection.

## Data Migration

If you need to migrate existing MySQL data to MongoDB:

1. Export your MySQL data to JSON format
2. Transform the data to match the MongoDB document structure
3. Import the data into MongoDB collections using `mongoimport` tool or programmatically with the MongoDB PHP library

## Troubleshooting

If you encounter connection issues:

1. Verify your IP address is whitelisted in MongoDB Atlas Network Access
2. Check your username and password
3. Ensure the MongoDB PHP extension is properly installed
4. Check the error logs for specific error messages
