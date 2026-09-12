<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\ContactForm;
use app\models\LoginForm;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\base\Security;
use yii\mail\MailerInterface;
use yii\web\Controller;
use yii\web\ErrorAction;

// ws_env() reads .env for the connection check; config/db.php loads it too.
require_once __DIR__ . '/../config/env.php';
use yii\web\Response;

class SiteController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly MailerInterface $mailer,
        private readonly Security $security,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'transparent' => true,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    /**
     * Database connection check.
     *
     *   GET /api/db-check  → 200 {"ok":true,  ...}   connection works
     *                      → 503 {"ok":false, ...}   it does not, and why
     *
     * An expert can confirm the credentials work without reading any code:
     *
     *   curl -fsS http://localhost/api/db-check
     *
     * The 503 makes `curl -f` exit non-zero, so a whole room can be swept in one
     * loop. Every WSC2026 template answers the same check in the same shape.
     *
     * The connection itself is configured entirely in .env (local) and .env.prod
     * (deployed) — see config/db.php. Nothing is hardcoded here.
     */
    public function actionDbCheck()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $host     = ws_env('DB_HOST');
        $database = ws_env('DB_DATABASE');

        // Repeat the settings WITHOUT the password, so a failure says which
        // database was unreachable and a success cannot leak a credential.
        $base = [
            'driver'   => 'mysql',
            'host'     => $host,
            'port'     => (int) (ws_env('DB_PORT') ?? 3306),
            'database' => $database,
            'user'     => ws_env('DB_USERNAME'),
        ];

        // No credentials at all is almost always a .env that never arrived,
        // rather than a database that is down. Say so plainly.
        if ($host === null || $database === null) {
            \Yii::$app->response->statusCode = 503;

            return $base + [
                'ok'    => false,
                'error' => 'DB_HOST or DB_DATABASE is not set',
                'hint'  => 'Local development: cp .env.example .env. Deployed: the platform '
                         . 'writes .env.prod, which the entrypoint copies over .env at startup.',
            ];
        }

        try {
            $started = microtime(true);

            // A real round trip, not just "the connection object was built".
            $version = \Yii::$app->db->createCommand('SELECT VERSION()')->queryScalar();
            $latency = (int) round((microtime(true) - $started) * 1000);

            // Asked separately, so the check still passes on a correctly
            // configured but not-yet-migrated database: working credentials and a
            // present schema are two different questions.
            $table   = \Yii::$app->db->tablePrefix . 'visitor';
            $present = \Yii::$app->db->getTableSchema($table, true) !== null;

            return $base + [
                'ok'             => true,
                'server_version' => $version,
                'latency_ms'     => $latency,
                'demo_table'     => $present ? $table . ' present' : $table . ' missing',
            ];
        } catch (\Throwable $e) {
            \Yii::$app->response->statusCode = 503;

            return $base + [
                'ok'    => false,
                'error' => $e->getMessage(),
                'code'  => (string) $e->getCode(),
                'hint'  => 'Check the DB_* values in .env (local) or .env.prod (deployed). '
                         . 'Access denied means wrong credentials; connection refused or a '
                         . 'timeout means the host, port or network is wrong.',
            ];
        }
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm($this->security);

        if ($model->load($this->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', ['model' => $model]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact(): Response|string
    {
        $model = new ContactForm();

        $contact = $model->load($this->request->post()) && $model->contact(
            $this->mailer,
            Yii::$app->params['adminEmail'],
            Yii::$app->params['senderEmail'],
            Yii::$app->params['senderName'],
        );

        if ($contact) {
            Yii::$app->session->setFlash(
                'success',
                'Thank you for contacting us. We will respond to you as soon as possible.',
            );

            return $this->refresh();
        }

        return $this->render('contact', ['model' => $model]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout(): string
    {
        return $this->render('about');
    }
}
