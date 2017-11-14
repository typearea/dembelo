--TEST--
Version errors
--FILE--
<?php

try {
    ast\parse_code('<?php ...');
} catch (LogicException $e) {
    echo $e->getMessage(), "\n";
}

try {
    ast\parse_code('<?php ...', $version=100);
} catch (LogicException $e) {
    echo $e->getMessage(), "\n";
}

?>
--EXPECTF--
No version specified. Current version is %d. All versions (including experimental): {30, 35, %s}
Unknown version 100. Current version is %d. All versions (including experimental): {30, 35, %s}
