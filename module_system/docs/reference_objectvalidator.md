# Object-Validator
In Kajona 4.6 we introduced the possibility to register an object validator for an object which is derived from class \Kajona\System\System\Model.

In Kajona 4.0 we already introduced field validators which validate the values of a single form field (e.g. the validator '\Kajona\System\System\Date_validator' validates if the value in the field is a date).

With the introduction of the object validator it ispossible to validate fields of an object which are dependent on each other (e.g. start date of an object must be before end date of an object).

Object validators in Kajona are being executed during validation of a form (after the field validators have been executed)
Object validators can also be used in a different context (e.g. in unit tests) by just creating an instance of that object validator and executing the "validateObject" method.

Each object validator needs to extend the abstract base class ``ObjectvalidatorBase`` (module_system/system/ObjectvalidatorBase.php).

The base class contains exactly one abstract method "validateObject" which gets as parameter the object to be validated. This method validateObject must return a boolean value to indicate whether the object is valid or not.
If no validation error occurred, the is method must return false.
If validation errors occur, the method must return true and optionally set validation-errors (addValidationError()) using an assiciatives array of format
	
	array("<messageKey>" => array()).

Each key in the array contains an array of validation messages.

Below the base class ObjectvalidatorBase :
 
	abstract class ObjectvalidatorBase {
		private $arrValidationMessages = array();
		/**
		 * Validates the passed object.
		 *
		​ * Return a boolean value to indicate whether the obejct is valid or not.
		 * If you want to provide additional error-messages (e.g. for a form), add them via
		* $this->addValidationError(key, error)
		* while key could be the name of the formentry.
		*
		* @abstract
		* @param \Kajona\System\System\Model $objObject - the model object to the given form
		* @return bool
		*/
		public abstract function validateObject(\Kajona\System\System\Model $objObject);

		/** * Adds an additional, user-specific validation-error to the current list of errors.
		*
		* @param string $strEntry
		* @param string $strMessage
		* @return void
		*/
		public function addValidationError($strEntry, $strMessage) {
		if(!array_key_exists($strEntry, $this->arrValidationMessages)) {
		$this->arrValidationMessages[$strEntry] = array();
		}
		$this->arrValidationMessages[$strEntry][] = $strMessage;
		
		}
		/**
		* @return array
		*/
		public function getArrValidationMessages() {
		return $this->arrValidationMessages;
		​}
	​ }
 

Like field validators, a concrete object validator class needs to be created in the folder "system/validators" of your module (e.g. ``module_yourmodule/system/validators/ObjectvalidatorYourobjectvalidator.php``).

 
	class ObjectvalidatorYourobjectvalidator extends ObjectvalidatorBase {
	    public function validateObject(\Kajona\System\System\Model $objObject) {
	        // Validation code here
	    }
	} 
	 

To register the object validator you have created  you need to add the class-annotation "@objectValidator" to a class which is derived from the class \Kajona\System\System\Model. The value for this annotation is the classname of the created validator class.
 
	/**
	 * .....
	 * @objectValidator Yourmodule\System\Validators\ObjectvalidatorYourobjectvalidator
	 * .....
	 */
	class YourmoduleMyobject extends \Kajona\System\System\Model {
	    //...object code here...
	} 
 
A concrete example for an object validator is implemented  for class "NewsNews" in module "module_news".

class: ``module_news/system/NewsNews.php``

object validator: ``module_news/system/validators/NewsNewsObjectvalidator.php``

 
