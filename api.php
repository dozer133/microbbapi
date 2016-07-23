<?php

  /*
    MicroBBAPI
    A very tiny rest API for phpBB
    Author: https://github.com/dozu
  */

  header('Content-Type: application/json');

  // split the request, remove api/v<int>
  $request = array_slice(explode('/', $_SERVER['REQUEST_URI']), 3);
  $input = json_decode(file_get_contents('php://input'), true);

  // phpbb stuff
  @define('DEBUG', false);
  define('IN_PHPBB', true);
  $phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
  $phpEx = substr(strrchr(__FILE__, '.'), 1);
  include($phpbb_root_path . 'common.' . $phpEx);

  $user->session_begin();
  $auth->acl($user->data);
  $user->setup();

  function response($statusCode, $data)
  {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
  }

  // router functionality
  // request => method => function
  $router = [];

  $router['login']['POST'] = function() {
      global $auth,  $input;

      $result = $auth->login($input['username'], $input['password']);
      $result['status'] == LOGIN_SUCCESS ? response(200, "LOGIN_SUCCESS") : response(401, "LOGIN_FAIL");
    };

  $router['user']['GET'] = function () {
      global $db, $request;

      $sql = 'SELECT user_id, username
        FROM ' . USERS_TABLE . '
        WHERE user_id = ' . $db->sql_escape($request[1]);
      $result = $db->sql_query($sql);
      $row = $db->sql_fetchrow($result);
      $db->sql_freeresult($result);

      response(200, $row);
  };

  // check if the method and function exists in the router
  if (!array_key_exists($request[0], $router) || !array_key_exists($_SERVER['REQUEST_METHOD'], $router[$request[0]]) || !is_callable($router[$request[0]][$_SERVER['REQUEST_METHOD']]))
    response(400, "BAD_REQUEST");

  // run the function
  $router[$request[0]][$_SERVER['REQUEST_METHOD']]();

?>
