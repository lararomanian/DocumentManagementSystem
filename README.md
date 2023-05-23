Commands

composer install

php artisan migrate:fresh -> clears the database
php artisan passport:install ->generate OaUth Keys

php artisan db:seed -> generates the admin account and sets the permissions

Admin Credentials:

    email = admin@email.com
    password = password

In the .env File replace the previous data with the following ( this is for sending and receiving emails)


    MAIL_MAILER=smtp
    MAIL_HOST=sandbox.smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=a1ccb309ec6bb9
    MAIL_PASSWORD=b64bf41559aec9
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS="noreply@dms.com"
    MAIL_FROM_NAME="Document Management System"


