# Json Deserialize Utility
JSON Deserialize is an abstract class that enables JSON deserialization into a specific class. Requires 
&gt;=PHP 8

## Usage
myModel.php
```php
/** @method myModel static jsonDeserialize() */
class myModel extends \gcgov\jsonDeserialize\jsonDeserialize {

	public string $varA = '';
	public string $varB = '';
	
}
```

myController.php
```php
class myController {
    
    public function post() {
        
        $jsonString = '{ "varA":"A", "varB":"B" }';
        $myModel = myModel::jsonDeserialize( $jsonString );
        
        echo $myModel->varA;
        echo "\n";
        echo $myModel->varB;
    }
    
}
```

Output:
```
A
B
```