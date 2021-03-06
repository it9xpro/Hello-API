<?php

namespace App\Containers\User\Services;

use App\Containers\ApiAuthentication\Services\ApiAuthenticationService;
use App\Containers\User\Contracts\UserRepositoryInterface;
use App\Containers\User\Exceptions\AccountFailedException;
use App\Port\Service\Abstracts\Service;
use Exception;
use Illuminate\Support\Facades\Hash;

/**
 * Class CreateUserService.
 *
 * @author Mahmoud Zalt <mahmoud@zalt.me>
 */
class CreateUserService extends Service
{

    /**
     * @var \App\Containers\User\Contracts\UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var \App\Containers\ApiAuthentication\Services\ApiAuthenticationService
     */
    private $authenticationService;

    /**
     * CreateUserService constructor.
     *
     * @param \App\Containers\User\Contracts\UserRepositoryInterface              $userRepository
     * @param \App\Containers\ApiAuthentication\Services\ApiAuthenticationService $authenticationService
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ApiAuthenticationService $authenticationService
    ) {
        $this->userRepository = $userRepository;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param            $email
     * @param            $password
     * @param            $name
     * @param bool|false $login
     *
     * @return  mixed
     */
    public function byCredentials($email, $password, $name, $login = false)
    {
        $hashedPassword = Hash::make($password);

        // create new user
        $user = $this->create([
            'name'     => $name,
            'email'    => $email,
            'password' => $hashedPassword,
        ]);

        if ($login) {
            // login this user using it's object and inject it's token on it
            $user = $this->authenticationService->loginFromObject($user);
        }

        return $user;
    }

    /**
     * @param      $visitorId device ID (example: iphone UUID, Android ID)
     * @param null $device
     * @param null $platform
     *
     * @return  mixed
     */
    public function byVisitor($visitorId, $device = null, $platform = null)
    {
        // create new user
        $user = $this->create([
            'visitor_id' => $visitorId,
            'device'     => $device,
            'platform'   => $platform,
        ]);

        return $user;
    }


    /**
     * @param $data
     *
     * @return  mixed
     */
    private function create($data)
    {
        try {
            // create new user
            $user = $this->userRepository->create($data);
        } catch (Exception $e) {
            throw (new AccountFailedException())->debug($e);
        }

        return $user;
    }

}
