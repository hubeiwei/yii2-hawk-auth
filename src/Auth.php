<?php

namespace hubeiwei\yii2Hawk;

use Shawm11\Hawk\Server\BadRequestException as HawkBadRequestException;
use Shawm11\Hawk\Server\Server as HawkServer;
use Shawm11\Hawk\Server\ServerException as HawkServerException;
use Shawm11\Hawk\Server\UnauthorizedException as HawkUnauthorizedException;
use yii\filters\auth\AuthMethod;
use yii\helpers\ArrayHelper;
use yii\web\UnauthorizedHttpException;

class Auth extends AuthMethod
{
    public $header = 'Authorization';

    /**
     * @var string user AppSecret attribute
     */
    public $appSecretAttribute = 'app_secret';

    /**
     * @param \yii\web\User $user
     * @param \yii\web\Request $request
     * @param \yii\web\Response $response
     * @return \yii\web\IdentityInterface
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function authenticate($user, $request, $response)
    {
        $url = '/' . $request->getPathInfo();
        $queryString = $request->getQueryString();
        if ($queryString) {
            $url .= '?' . $queryString;
        }

        $requestData = [
            'method' => $request->getMethod(),
            'port' => $request->getPort(),
            'host' => $request->getHostName(),
            'url' => $url,
            'authorization' => $request->getHeaders()->get($this->header),
        ];

        $credentialsFunc = function ($id) use ($user, $response) {
            /* @var $class \yii\web\IdentityInterface */
            $class = $user->identityClass;
            $identity = $class::findIdentityByAccessToken($id, get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            }

            $credentials = [
                'id' => $id,
                'key' => ArrayHelper::getValue($identity, $this->appSecretAttribute),
                'algorithm' => 'sha256',
                'identity' => $identity,
            ];
            return $credentials;
        };

        try {
            $hawkServer = new HawkServer;
            $result = $hawkServer->authenticate($requestData, $credentialsFunc);
        } catch (HawkBadRequestException $e) {
            $this->handleFailure($response);
        } catch (HawkUnauthorizedException $e) {
            $response->getHeaders()->set('WWWW-Authenticate', $e->getWwwAuthenticateHeader());
            $this->handleFailure($response, $e->getMessage());
        } catch (HawkServerException $e) {
            $this->handleFailure($response, $e->getMessage());
        }

        $credentials = $result['credentials'];
        /** @var \yii\web\IdentityInterface $identity */
        $identity = $credentials['identity'];
        $user->login($identity);
        return $identity;
    }

    /**
     * @param \yii\web\Response $response
     * @param string $message
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function handleFailure($response, $message = 'Your request was made with invalid credentials.')
    {
        throw new UnauthorizedHttpException($message);
    }
}
