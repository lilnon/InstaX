FROM php:8.2-apache

# ติดตั้งส่วนขยายที่จำเป็น
RUN docker-php-ext-install pdo pdo_mysql

# คัดลอกโค้ดเว็บเข้าไปใน Container
COPY . /var/www/html/

# เปิดพอร์ต 80
EXPOSE 80

# รัน Apache Server
CMD ["apache2-foreground"]
