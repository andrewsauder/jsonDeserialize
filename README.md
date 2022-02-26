# Json Deserialize Utility

JSON Deserialize is an abstract class that enables JSON deserialization into a specific class. Simply extend the jsonDeserialize class and then call the static jsonDeserialize method. Requires all properties to be typed. Array type will be
determined by a PHPDoc definition.

Requires &gt;=PHP 8

## Usage

myModel.php

```php
/** @method myModel static jsonDeserialize() */
class myModel extends \andrewsauder\jsonDeserialize\jsonDeserialize {

	public int $varA = 1;
	
	public string $varB = '';
	
	#[excludeJsonDeserialize]
	public string $varC = 'not deserialized';
	
	/** @var string[]  */
	public array $varD = '';
}
```

myController.php

```php
class myController {
    
    public function post() {
        
        $jsonString = '{ "varA":"A", "varB":"B", "varC":"C", "varD":[ "D1", "D2", "D3" ] }';
        $myModel = myModel::jsonDeserialize( $jsonString );
        
        echo $myModel->varA;
        echo "\n";
        echo $myModel->varB;
        echo "\n";
        echo $myModel->varC;
	foreach( $myModel->varD as $i=>$v) {
		echo "\n";
        	echo $myModel->varD[ $i ];
	}
      
    }
    
}
```

Output:

```
A
B
not deserialized
D1
D2
D3
```

## Debug Logging

To enable debug logging (useful for determining missing properties), run these two line prior to deserializing. All objects deserialized after will include debug logging to a new log file in the provided path.

```php
\andrewsauder\jsonDeserialize\config::setDebugLogging( true );
\andrewsauder\jsonDeserialize\config::setDebugLogPath( 'C:/inetpub/logs' );
```