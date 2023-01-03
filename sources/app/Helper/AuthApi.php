<?php

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    function authapi()
    {
        $date = date_create();

        $key = 'restfull-api-pondo';
        $payload = [
            'iss'       => 'pondo.co.id',
            'cons_id'   => 'mrpondofr',
            'timestamp' => date_timestamp_get($date)
        ];

        /**
         * IMPORTANT:
         * You must specify supported algorithms for your application. See
         * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
         * for a list of spec-compliant algorithms.
         */
        $jwt = JWT::encode($payload, $key, 'HS256');
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

        return json_encode($decoded);
        /*
        NOTE: This will now be an object instead of an associative array. To get
        an associative array, you will need to cast it as such:
        */

        $decoded_array = (array) $decoded;


        /**
         * You can add a leeway to account for when there is a clock skew times between
         * the signing and verifying servers. It is recommended that this leeway should
         * not be bigger than a few minutes.
         *
         * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
         */
        JWT::$leeway = 60; // $leeway in seconds
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    }

    function decode($token)
    {
        return $token;
    }