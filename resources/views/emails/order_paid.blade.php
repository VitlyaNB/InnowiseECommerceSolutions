<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ подтвержден</title>
</head>
<body>
<h1>Спасибо за заказ!</h1>
<p>Ваш заказ #{{ $order->id }} успешно оплачен.</p>
<p>Сумма: {{ number_format($order->totalAmount, 2) }} BYN</p>
</body>
</html>
