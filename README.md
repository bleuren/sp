# Laravel-SSO(Sp)

## 安裝

### 安裝相依套件
`composer install`

### 產生key
`php artisan key:generate`

### 建立所需資料表
`php artisan migrate`

### 將SSO-Idp產生出來的Client ID及Client secret貼到.env [CLIENT_ID, CLIENT_SECRET]

### 啟動服務
`php artisan serve --port 8080`

## Done!