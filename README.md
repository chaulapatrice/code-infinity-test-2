# Setup 

### 1. Clone 
```
git clone https://github.com/chaulapatrice/code-infinity-test-2.git
```
```
cd code-infinity-test-2
```

### 2. Run containers


Run the following  command to run the web container

```
docker-compose up -d
```

### 3. Initialize the database 

Run the following command to login into the `web` container.

```
docker-compose exec web /bin/sh
```

Install dependencies

```
composer update
```

Create database 

```
php init_database.php
```

### 4. View in the browser
The application is accessible at http://localhost:8888