<?php
$homeUrl = function_exists('base_url') ? base_url('/') : '/';
$loginUrl = function_exists('base_url') ? base_url('dashboard') : '/dashboard';
$logoUrl = function_exists('base_url') ? base_url('resources/img/logo.png') : '/resources/img/logo.png';
$detail = ENVIRONMENT !== 'production' && ! empty($message) ? $message : 'La solicitud no pudo procesarse correctamente.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud no valida</title>
    <style>
        :root {
            --teal: #00695c;
            --blue: #1565c0;
            --surface: #ffffff;
            --text: #1f2933;
            --muted: #607d8b;
            --border: #d8e2e0;
            --page: #f4f8f7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--page);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            flex-direction: column;
        }

        header,
        footer {
            background: var(--teal);
            color: #fff;
        }

        header {
            height: 78px;
            display: flex;
            align-items: center;
            padding: 0 28px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .18);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 20px;
            font-weight: 700;
        }

        .brand img {
            height: 52px;
            width: auto;
        }

        main {
            flex: 1;
            display: grid;
            place-items: center;
            padding: 48px 20px;
        }

        .panel {
            width: min(920px, 100%);
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 16px 38px rgba(0, 77, 64, .10);
            padding: 46px;
            text-align: center;
        }

        .code {
            color: var(--teal);
            font-size: 88px;
            line-height: 1;
            font-weight: 700;
            margin: 0 0 14px;
        }

        h1 {
            margin: 0;
            font-size: 34px;
            font-weight: 700;
        }

        p {
            max-width: 620px;
            margin: 18px auto 0;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.5;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn {
            border: 0;
            border-radius: 6px;
            padding: 12px 18px;
            min-width: 150px;
            color: #fff;
            background: var(--teal);
            text-decoration: none;
            font-weight: 700;
            display: inline-block;
        }

        .btn.secondary {
            background: var(--blue);
        }

        footer {
            padding: 18px 28px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <div class="brand">
            <img src="<?= esc($logoUrl, 'attr') ?>" alt="Logo">
            <span>Portal de soporte</span>
        </div>
    </header>

    <main>
        <section class="panel" aria-labelledby="page-title">
            <div class="code">400</div>
            <h1 id="page-title">Solicitud no valida</h1>
            <p><?= esc($detail) ?></p>
            <div class="actions">
                <a class="btn" href="<?= esc($loginUrl, 'attr') ?>">Ir al inicio</a>
                <a class="btn secondary" href="<?= esc($homeUrl, 'attr') ?>">Volver al portal</a>
            </div>
        </section>
    </main>

    <footer>Derechos reservados <?= date('Y') ?></footer>
</body>
</html>
