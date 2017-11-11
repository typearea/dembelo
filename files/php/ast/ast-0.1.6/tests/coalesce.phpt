--TEST--
Null-coalesce operator
--FILE--
<?php

require __DIR__ . '/../util.php';

$code = <<<'PHP'
<?php
$a ?? $b;
PHP;

echo ast_dump(ast\parse_code($code, $version=35)), "\n";
echo ast_dump(ast\parse_code($code, $version=40)), "\n";

?>
--EXPECTF--
Deprecated: ast\parse_code(): Version 35 is deprecated in %s on line %d
AST_STMT_LIST
    0: AST_COALESCE
        left: AST_VAR
            name: "a"
        right: AST_VAR
            name: "b"
AST_STMT_LIST
    0: AST_BINARY_OP
        flags: BINARY_COALESCE (260)
        left: AST_VAR
            name: "a"
        right: AST_VAR
            name: "b"
