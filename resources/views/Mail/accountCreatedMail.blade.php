<!DOCTYPE html>
<html>
<head>
    <title>Account Created</title>
</head>
<body>
    <h1>Account Created</h1>
    <p>Hi {{$data["name"]}},</p>
    <p>Your account has been created. Please use the email and password below to login to your account.</p>
    <p>Email: {{$data["email"]}}</p>
    <p>Password: {{$data["password"]}}</p>
    <p>Once you have logged in you can change your password by clicking on the link below.</p>
    <a href="/">Reset Password</a>
    <br>
    <p>Click on the link below to login to your account.</p>
    <a href="/">Login</a>
    <br>
    <p>Thank you,</p>
    <p>Admin</p>
</body>
</html>
