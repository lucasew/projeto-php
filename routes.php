<?php
require "prelude.php";


function is_empty_string(string $s) {
    return $s == "";
}

$INPUT_DATA = array_merge_recursive($_GET, $_POST);
$ROUTE = parse_url($_SERVER["REQUEST_URI"])["path"];

function user_get() {
    global $INPUT_DATA;
    $login_user = $INPUT_DATA["login_user"] ?? null;
    $login_password = $INPUT_DATA["login_password"] ?? null;

    if (!is_null($login_user) && !is_null($login_password)) {
        if (pw_verify($login_user, $login_password)) {
            ["uid" => $uid, "role" => $role] = db_get_result(db_stmt(
                "select uid, role from users where username = ?", "s", "$login_user"
            ));
            return [
                "uid" => $uid,
                "is_admin" => $role == "ADMIN",
                "username" => $login_user
            ];
        } else {
            respond_error(401, "invalid authentication");
        }
    }
    return null;
}

function user_must_authenticated() {
    $user = user_get();
    if (is_null($user)) {
        respond_error(401, "unauthorized");
    }
    return $user;
}

function user_must_admin() {
    $user = user_get();
    if (is_null($user) || !$user["is_admin"]) {
        respond_error(401, "unauthorized");
    }
}

/**
 * bring required variables to scope then require the new file
 */
function execphp(string $script) {
    global $INPUT_DATA, $ROUTE;
    require $script;
    // doesn't pass this part
    die();
}

/**
 * create a route that matches anything starting with $base_route
 */
function use_route(string $base_route, string $handler_script) {
    global $ROUTE;
    if (str_starts_with($ROUTE, $base_route)) {
        $ROUTE = substr($ROUTE, strlen($base_route));
        execphp($handler_script);
    }
}

/**
 * create a route that matches exactly $selected_route
 */
function exact_route(string $selected_route, string $handler_script) {
    global $ROUTE;
    if (strcmp($ROUTE, $selected_route) == 0) {
        execphp($handler_script);
    }
}

/**
 * create a route with route params, like /users/:user/info 
 * and pass the route param with input_data
 */
function exact_with_route_param(string $selected_route, string $handler_script) {
    global $INPUT_DATA, $ROUTE;
    $preprocess = function (string $raw_route) {
        $splitted = preg_split("/\//", $raw_route);
        $splitted = array_filter($splitted, function($v, $k) {
            return !is_empty_string($v);
        }, ARRAY_FILTER_USE_BOTH);
        return array_values($splitted);
    };
    $params_parts = $preprocess($selected_route);
    $route_parts = $preprocess($ROUTE);
    // log_httpd(json_encode($params_parts));
    // log_httpd(json_encode($route_parts));

    $extra_params = [];
    if (count($params_parts) == count($route_parts)) {
        for ($i = 0; $i < count($params_parts); $i++) {
            if (str_starts_with($params_parts[$i], ":")) {
                $extra_params[substr($params_parts[$i], 1)] = $route_parts[$i];
            } else {
                if (strcmp($params_parts[$i], $route_parts[$i]) != 0) {
                    return;
                }
            }
        }
    } else {
        return;
    }
    $INPUT_DATA = array_merge_recursive($INPUT_DATA, $extra_params);
    execphp($handler_script);
}


// healthcheck
exact_route("/healthcheck", "routes/healthcheck.php");

// demos
exact_with_route_param("/api/demo/say/:word/", "routes/api/demo/say.php");
exact_with_route_param("/api/demo/say/:word/wednesday", "routes/api/demo/say.php");
exact_route("/api/demo/db", "routes/api/demo/db.php");
use_route("/api/demo/inspect", "routes/api/demo/inspect.php");

// business logic
exact_route("/api/admin/db_bootstrap", "routes/api/admin/db_bootstrap.php");
exact_route("/api/user/signup", "routes/api/user/signup.php");
exact_route("/api/user/whoami", "routes/api/user/whoami.php");
exact_route("/api/site/create", "routes/api/site/create.php");
exact_route("/api/site/list", "routes/api/site/list.php");

// fallback
respond_error(404, "page not found");

?>