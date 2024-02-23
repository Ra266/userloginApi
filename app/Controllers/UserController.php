<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class UserController extends BaseController
{
  use ResponseTrait;

  public function userSign()
  {
    $user = new UserModel();
    $json = $this->request->getJSON();
    $user_name = $json->name;
    $user_username = $json->username;
    $user_email = $json->email;
    $user_password = $json->password;

    $data = [
      'user_name' => $user_name,
      'user_username' => $user_username,
      'user_email' => $user_email,
      'user_password' => $user_password,
      'userheaderid' => $user_username,
      'userheaderpassword' => $user_password,


    ];
    if ($user->insert($data)) {
      $response = [
        'error' => [
          'errorCode' => 00,
          'errorMessage' => '',

        ],
        'status' => '201',
        'message' => 'user sign up successfully',
      ];
      return $this->respondCreated($response);
    }
  }
  public function login()
  {
    $user = new UserModel();

    //create token for user login
    date_default_timezone_set("UTC");
    $t = microtime(true);
    $micro = sprintf("%03d", ($t - floor($t)) * 1000);

    $timestamp = date($micro);
    $string_value = substr(str_shuffle(str_repeat('qwertyuioplkjhgfdsazxcvbnmQWERTYUIOPASDFGHJKLMNBVCXZ0123456789', 10)), 10, 10);

    if ($this->request->hasHeader('userid') && $this->request->hasHeader('password')) {
      $userheaderid = $this->request->getHeaderLine('userid');
      $userheaderpass = $this->request->getHeaderLine('password');
      $userheaderiddata = $user->where('userheaderid', $userheaderid)->first();
      if (!empty($userheaderiddata)) {
        $userheaderpassword = $user->where('userheaderpassword', $userheaderpass)->first();
        if (!empty($userheaderpassword)) {
          // $json = $this->request->getJSON();
          // $name = $json->name;
          // $password = $json->password;

          if ($username = $user->where('user_username', $userheaderiddata['user_username'])->first()) {
            if ($password = $user->where('user_password', $userheaderiddata['user_password'])->find()) {
              // print_r($username[0]['user_username']);
              // die;
              $tp = $username['user_username'];
              $tokenid = sha1(base64_encode(sha1(base64_decode($string_value) . $timestamp . $tp, true)));
              // print_r($tokenid);
              // die;

              $response = [
                'error' => [
                  'errorCode' => 00,
                  'errorMessage' => '',

                ],
                'status' => 200,
                'message' => 'user login successfully',
                'token' => $tokenid,
              ];
              // print_r($response['token']);
              // die;
              $user->where('user_username', $tp)->set('user_token', $response['token'])->update();
              return $this->respond($response);
            } else {
              $response = [
                'error' => [
                  'errorCode' => 400,
                  'errorMessage' => 'Please enter a correct password',

                ],

              ];
              return $this->respond($response);
            }
          } else {
            $response = [
              'error' => [
                'errorCode' => 400,
                'errorMessage' => 'Please enter a correct username',
              ],
            ];
            return $this->respond($response);
          }
        } else {
          $response = [
            'error' => [
              'errorCode' => 400,
              'errorMessage' => 'Please enter a correct Header password',
            ],
          ];
          return $this->respond($response);
        }
      } else {
        $response = [
          'error' => [
            'errorCode' => 400,
            'errorMessage' => 'Please enter a correct Header username ',
          ],
        ];
        return $this->respond($response);
      }
    } else {
      $response = [
        'error' => [
          'errorCode' => 400,
          'errorMessage' => 'Access denied ',
        ],
      ];
      return $this->respond($response);
    }
  }

  public function profile()
  {
    $user = new UserModel();

    $token = $this->request->getHeaderLine('token');

    $dbtoken = $user->where('user_token', $token)->first();
    if ($dbtoken) {
      $response = array(
        'error' => [
          'errorCode' => 00,
          'errorMessage' => '',
        ],
        'status' => '400',
        'Result' => [
          'name' => $dbtoken['user_name'],
          'username' => $dbtoken['user_username'],
          'email' => $dbtoken['user_email'],


        ],
      );
      return $this->respond($response);
    } else {
      $response = [
        'error' => [
          'errorCode' => 400,
          'errorMessage' => 'expired Token',
        ],
      ];
      return $this->respond($response);
    }
  }
}
