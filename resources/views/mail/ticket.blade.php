<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
id - {{$id}}
<br>
@if(!empty($email))
email - {{$email}}
<br>
@endif
name - {{$name}}
<br>
phone - {{$phone}}
<br>
@if(!empty($messages))
message - {{$messages}}
<br>
@endif
@if(!empty($file))
file - {{$file}}
<br>
@endif
created_at - {{$created_at}}
</body>
</html>
