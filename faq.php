<?php

require_once 'config.php';
require_once 'includes/functions.php';

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content faq-page">

    <section class="faq-hero">
        <p class="section-label">FAQ</p>
        <h1>Часто задаваемые вопросы</h1>
        <p>
            Кратко о том, как устроен Music Rating: композиции, релизы,
            оценки, рецензии, рейтинг и возможности пользователя.
        </p>
    </section>

    <section class="faq-list">

        <details class="faq-item" open>
            <summary>Что это за сайт?</summary>

            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>
                        Это музыкальная площадка, где пользователи могут смотреть композиции,
                        изучать исполнителей, ставить оценки и оставлять подробные рецензии.
                        Главная цель сайта — собрать музыку, мнения слушателей и рейтинг
                        композиций в одном удобном месте.
                    </p>
                </div>
            </div>
        </details>

        <details class="faq-item">
            <summary>Как добавить музыкальную композицию?</summary>

            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>
                        Добавление композиций доступно администратору. Через кнопку
                        «Добавить композицию» можно указать название трека, исполнителя,
                        жанр, описание, обложку и аудиофайл. После сохранения композиция
                        появляется на сайте и может участвовать в рейтинге.
                    </p>
                </div>
            </div>
        </details>

        <details class="faq-item">
            <summary>Как релизы попадают на сайт?</summary>

            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>
                        Релизы добавляются через административную часть сайта. Сначала
                        создаётся или выбирается исполнитель, затем к нему привязывается
                        композиция. Так каждая песня остаётся связанной со своим автором,
                        жанром, рейтингом и рецензиями.
                    </p>
                </div>
            </div>
        </details>

        <details class="faq-item">
            <summary>Как формируется рейтинг композиции?</summary>

            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>
                        Рейтинг формируется по пользовательским оценкам. Чем выше средняя
                        оценка композиции, тем выше она отображается в разделе «Рейтинг».
                        Это помогает быстро находить самые сильные и заметные треки сайта.
                    </p>
                </div>
            </div>
        </details>

        <details class="faq-item">
            <summary>Какие возможности есть у пользователя?</summary>

            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <ul>
                        <li>просматривать композиции и страницы исполнителей;</li>
                        <li>искать и фильтровать музыкальные записи;</li>
                        <li>ставить оценки композициям;</li>
                        <li>писать рецензии и читать мнения других слушателей;</li>
                        <li>смотреть свою активность в личном профиле.</li>
                    </ul>
                </div>
            </div>
        </details>

        <details class="faq-item">
            <summary>Что такое рецензия на композицию?</summary>

            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>
                        Рецензия — это развёрнутый отзыв пользователя о композиции.
                        В ней можно написать, чем понравился трек, что можно улучшить,
                        насколько сильны звук, атмосфера, текст, исполнение и общее
                        впечатление от релиза.
                    </p>
                </div>
            </div>
        </details>

        <details class="faq-item">
            <summary>Где посмотреть лучшие композиции?</summary>

            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>
                        Лучшие композиции отображаются на главной странице и в разделе
                        «Рейтинг». Там можно увидеть треки с наиболее высокими средними
                        оценками и перейти на страницу каждой композиции.
                    </p>
                </div>
            </div>
        </details>

        <details class="faq-item">
            <summary>Зачем нужна страница исполнителя?</summary>

            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>
                        На странице исполнителя собраны его релизы, средний рейтинг,
                        количество композиций и последние рецензии слушателей. Это помогает
                        быстро понять, какие работы есть у автора и как их оценивают
                        пользователи.
                    </p>
                </div>
            </div>
        </details>

    </section>

</main>

<?php require_once 'includes/footer.php'; ?>
