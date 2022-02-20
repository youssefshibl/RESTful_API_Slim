<?php

$app->add(function( $req , $res , $next){
    
    $res->getBody()->write("before");
    $res = $next($req , $res);
    $res->getBody()->write("after");
    return $res ;
});