<?php
/**
 * @var \Psr\Http\Message\RequestInterface $request
 * @var \Psr\Http\Message\ResponseInterface $response
 * @var array $context
 * @var array $config
 */
?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $response->getStatusCode(); ?> <?= $response->getReasonPhrase(); ?>
        | <?= $config['app_title'] ?? "MaplePHP" ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700&display=swap" rel="stylesheet">
    <style>
        html {
          font-size: 100%;
        }
        body {
          margin: 0;
          padding: 0;
          font-family: "Nunito", Helvetica, Arial, sans-serif;
          color: #FFF;
          background-color: #2f2f2f;
          background-size: 100% 100%, auto;
          background-image:
            linear-gradient(to bottom, rgba(255,255,255,0.05), rgba(0,0,0,0.08)),
            repeating-linear-gradient(
              135deg,
              rgba(255,255,255,0.06) 0px,
              rgba(255,255,255,0.06) 1px,
              transparent 1px,
              transparent 10px
            );
        }
        main, section {
          display: block;
        }

        h1, p {
          margin: 0;
        }

        h1 {
          font-size: clamp(4rem, 11vw, 10rem);
          line-height: 0.96em;
        }

        h1::after {
          width: clamp(2.42rem, 6.6vw, 5.6rem);
          content: "";
          display: block;
          border-bottom: 2px solid #FFF;
          margin: 1.2rem 0 2rem 0;
        }

        p {
          font-size: clamp(1.2rem, 2.6vw, 1.6rem);
          line-height: 1.4em;
          margin-bottom: 2rem;
        }

        a.button {
          font-size: 1.1rem;
          text-decoration: none;
          color: #000;
          display: inline-block;
          padding: 0.85rem 1.2rem;
          background: #FFF;
          border-radius: 10px;
        }

        a.button:hover, a.button:active {
          background: #F1F1F1;
        }

        main {
          height: 100vh;
          min-height: 520px;
          box-sizing: border-box;
          padding: 2rem 1rem;
          display: flex;
          align-items: center;
          justify-content: center;
        }

        section {
          width: 100%;
          max-width: 420px;
          margin-bottom: 4rem;
        }

    </style>
</head>
<body>
<main>
    <section>
        <h1><?= $response->getStatusCode(); ?></h1>
        <p><?php

        if(isset($context['message']) && is_string($context['message'])) {
            echo $context['message'];

        } else {
            echo match ($response->getStatusCode()) {
                403 => "Sorry, you are not allowed to access this page",
                404 => "Sorry, the page you are looking for could not be found",
                500 => "An unexpected error occurred.",
                default => $response->getReasonPhrase(),
            };
        }
        ?></p>
        <a class="button" href="<?= $request->getUri()->withPath("/")->withQuery(""); ?>">
            Go home
        </a>
    </section>
</main>
</body>
</html>