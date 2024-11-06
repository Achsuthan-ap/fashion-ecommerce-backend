
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <p>Good news!</p>
    <p>The product <strong>{{ $product->name }}</strong> is now back in stock. You can order it here:</p>
    <a href="{{ url('/product/' . $product->id) }}">View Product</a>
</body>
</html>

