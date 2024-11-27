<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Парсинг</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #333;
        }
        h2 {
            margin-top: 20px;
            color: #444;
        }
        h3 {
            margin-top: 15px;
            font-size: 1.2em;
            color: #555;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background-color: #fff;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        li strong {
            color: #007bff;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Парсинг цен товаров</h1>

    <form action="/" method="POST">
        @csrf
        <label for="url">Введите URL товара для отслеживания:</label>
        <input type="text" name="url" id="url" required>
        <button type="submit">Получить данные</button>
    </form>

    @if($errors->any())
        <div class="error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($price) && count($price) > 0)
        <h1>Товар добавлен для отслеживания</h1>
        <h2>Текущие цены:</h2>
                @if(count($price) > 0)
                    <ul>
                        @foreach($price as $merchant => $priceValue)
                            <li><strong>{{ $merchant }}:</strong> {{ number_format($priceValue, 2, ',', ' ') }} ₸</li>
                        @endforeach
                    </ul>
                @else
                    <p>Цены не найдены для этого товара.</p>
                @endif
    @else
        <p>Нет данных для отображения.</p>
    @endif

</body>
</html>
