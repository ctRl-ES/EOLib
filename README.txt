// S4X8 EOLib 1.0
// Marcos Vives Del Sol - 23/V/2012
// Licensed under a CC-BY-SA license

This is a PHP library intented to be used with the Spanish forum ElOtroLado.net. This library requires both a working and updated PHP (http://php.net/) enviroment.

It also uses the S4X8 HTTP 1.1 Library (included), which provides RAW access to HTTP requests and headers, so you can work with cookies in RAM instead of writing them to the hard drive, which is not only slower but also more insecure. It needs PHP Simple HTML DOM Parser (http://simplehtmldom.sourceforge.net/), which must be placed on the same folder as the EOLib.

Included is example.polekiller.php, a simple example program that constantly polls the forums to search for new threads, and then posts a message to it.

// EOF
