#Reference: Events

Starting with Kajona v4.5, the way how events are handled was rewritten from scratch.
Events may be used to react on special actions triggered by the system. For example, it's possible to be notified if a record is deleted, if a record is updated or if a user logs into the system for the first time.


All events handled by Kajona are identified using a string-based identifier, e.g. ``core.system.recordupdated``. In order to be notified in case of an event, you have to implement the generic interface ``interface_genericevent_listener``. Compared to a more type-safe event-interface (e.g. interface_record_deleted_listener", the generic approach reduced the coupling between modules and avoids hard-coded dependencies between packages
Example: If your faqs module wants to react on events triggered by the search, the ``interface_genericevent_listener`` is all you have to implement. If the interface was named ``interface_search_triggered`` and the interface is provided by the search-package, your faq implementation will fail if the search package is not available (due to an undefined interface).

##Handling events 
If you want to handle a certain event, you have to provide a listener and register the listener for this event. Let's say you want to be notified in case a record is deleted in order to write a line to a logfile. 
The listener you have to provide would be the following implementation:
 
	class class_module_logging_recorddeletedlistener implements interface_genericevent_listener {
	 public function handleEvent($strEventName, array $arrArguments) {     list($strSystemid, $strSourceClass) = $arrArguments;
	  class_logger::getInstance()->addLogRow("record delete, id: ".$strSystemid, class_logger::$levelInfo);
	  return true;
	 }
	}
	class_core_eventdispatcher::getInstance()->removeAndAddListener( "core.system.recorddeleted"​, new class_module_logging_recorddeletedlistener());


This class takes care of everything. The last lines registeres the listener at the class_core_eventdispatcher for the event identified by ``core.system.recorddeleted``. The listener implements the interface and drops a line to the logfile.

Kajona scans the filesystems for possible listener at startup, so you don't have to worry that your handler will be registered. All you have to stick to is placing your listener within the packages system-directory.

##Throwing events
Throwing an event is a piece of cake!
All you have to know is the identifier of an event. Let's keep to the example above: Let's notify listeners about a deleted record. Normally this event is handled by Kajona, but let's trigger it again:
 
	class_core_eventdispatcher::getInstance()->notifyGenericListeners( "core.system.recorddeleted", array($strSystemid, get_class($objRecordDeleted)));


All we do is fetching an instance of the core_eventdispatcher and calling the method ``notifyGenericListeners``. Arguments to this method are the identifier of the event and an array of arguments. This array of arguments will be passed to the registered listeners using the callback-method handleEvent.

Thats all.
You should now be able to provide and register event-listeners and to throw new events based on an identifier.




##Overview of core events

<table>
	<tbody>
		<tr>
			<th>Identifier</th>
			<th colspan="2" style="border-bottom: 1px solid #ccc;">
Description</th>
		</tr>
		<tr>
			<td rowspan="3" style="border-right: 1px solid #ccc; vertical-align: top;">core.system.request.startprocessing</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.6</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>bool $bitAdmin<br />
			string $strModule<br />
			string $strAction<br />
			string $strLanguageParam</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Invoked right before starting to process the current request. The event is triggered&nbsp;by the request-dispatcher right before the request is given over to the controller.</td>
		</tr>
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc; vertical-align: top;">core.system.request.endprocessing</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.6</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>bool $bitAdmin<br />
			string $strModule<br />
			string $strAction<br />
			string $strLanguageParam</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Invoked right before finishing to process the current request. The event is triggered&nbsp;by the request-dispatcher right before closing the session and passing back the response object.&nbsp;You may modify the request by accessing the response-object.</td>
		</tr>
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc; vertical-align: top;">core.system.request.aftercontentsend</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.6</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>class_request_entrypoint_enum $objEntrypoint</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Invoked right after sending the response back to the browser, but before starting to&nbsp;shut down the request.<br />
			This means you are not able to change the response anymore, also the session is already closed to&nbsp;keep other threads from waiting. Use this event to perform internal cleanups if required.</td>
		</tr>
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc;border-right: 1px solid #ccc; vertical-align: top;">core.system.recordupdated</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.5</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>class_model $objRecord <br />
			bool $bitRecordCreated
			</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Thrown whenever a record is updated to the database.<br/> The param $bitRecordCreated indicates
			if a record was created(true) of if is only being updated(false)</td>
		</tr>
		
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc;border-right: 1px solid #ccc; vertical-align: top;">core.system.objectassignmentsupdated</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.8</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>string[] $arrNewAssignments<br />
string[] $areRemovedAssignments<br />
string[] $areCurrentAssignments<br />
class_root $objObject<br />
string $strProperty<br /><br />return bool</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Triggered as soon as a property mapping to objects is updated. Therefore the event is triggered as soon
as assignments are added or removed from an object.
The event gets a list of all three relevant items: assignments added, assignments removed, assignments remaining.
The relevant object and the name of the changed property are passed, too.<br />Return a valid bool value, otherwise the transaction will be rolled back!</td>
		</tr>
		
		
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc;vertical-align: top;">core.system.recordcopied</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.5</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>string $strOldSystemid<br />
			string $strNewSystemid<br />
			class_model $objNewObjectCopy</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Called whenever a record was copied. Event will be fired BEFORE child objects are being copied. Useful to perform additional actions, e.g. update / duplicate foreign assignments.</td>
		</tr>
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc;vertical-align: top;">core.system.recordcopyfinished</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.6</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>string $strOldSystemid<br />
			string $strNewSystemid<br />
			class_model $objNewObjectCopy</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Called whenever a record was copied. Event will be fired AFTER child objects were copied. Useful to perform additional actions, e.g. update / duplicate foreign assignments.</td>
		</tr>
		
		
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc;vertical-align: top;">core.system.previdchanged</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.5</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>string $strSystemid&nbsp;<br />
			string $strOldPrevId<br />
			string $strNewPrevid</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Invoked every time a records status was changed.
                Please note that the event is only triggered on changes, not during a records creation.</td>
		</tr>		
		
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc;vertical-align: top;">core.system.statuschanged</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.8</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>string $strSystemid<br />
                class_root $objObject<br />
                string $intOldStatus<br />
                string $intNewStatus</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Thrown if a records parent-id changed, e.g. if a record is moved within a hierarchical tree.</td>
		</tr>
		
		
		
		
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc;vertical-align: top;">core.system.recorddeleted</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.5</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>string $strSystemid &nbsp;&nbsp;<br />
			string $strSourceClass</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Thrown as soon as record is deleted from the database. Listen to those events if you want to trigger additional cleanups or delete linked contents.<br />Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.</td>
		</tr>
		
		
		
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc;vertical-align: top;">core.system.recorddeleted.logically</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.8</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>string $strSystemid &nbsp;&nbsp;<br />
			string $strSourceClass</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Thrown as soon as record is deleted logically, so set inactive. The reocord is NOT removed from the database. Listen to those events if you want to trigger additional cleanups or delete linked contents.<br />Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.</td>
		</tr>
		
		
		
		
		<tr>
        			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc;vertical-align: top;">core.system.recordrestored.logically</td>
        			<td style="border-top: 1px solid #ccc;">Since</td>
        			<td style="border-top: 1px solid #ccc;">4.8</td>
        		</tr>
        		<tr>
        			<td>Arguments</td>
        			<td>string $strSystemid<br />
                        string $strSourceClass The class-name of the object deleted<br />
                        class_model $objObject The object which is being restored</td>
        		</tr>
        		<tr>
        			<td>Description</td>
        			<td>Called whenever a records is restored from the database.<br />The event is fired after the record was restored but before the transaction will be committed.<br />Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.</td>
        		</tr>
		
		
		
		
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc;vertical-align: top;">core.system.userfirstlogin</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.5</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>string $strUserid</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Thrown if a users logs into the system for the very first time. May be used to trigger initializations such as creating dashboard widgets or welcome messages.</td>
		</tr>
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc;vertical-align: top;">core.system.userlogin</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.5</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>string $strUserid</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Thrown if a users logs into the system. May be used to trigger initializations.</td>
		</tr>
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc;vertical-align: top;">core.system.userlogout</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.5</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>string $strUserid</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Thrown if a users logs out of the system. May be used to trigger cleanup actions.</td>
		</tr>
		<tr>
			<td style="border-right: 1px solid #ccc;vertical-align: top;">&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td rowspan="3" style="border-top: 1px solid #ccc; border-right: 1px solid #ccc;vertical-align: top;">core.search.objectindexed</td>
			<td style="border-top: 1px solid #ccc;">Since</td>
			<td style="border-top: 1px solid #ccc;">4.5</td>
		</tr>
		<tr>
			<td>Arguments</td>
			<td>class_model $objInstance<br />
			class_module_search_document&nbsp;​$objSearchDocument</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Thrown as soon as an object is indexed by the search. Listen to this event if you want to add additional keywords to the objects' search index entry.</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td colspan="2">&nbsp;</td>
		</tr>
	</tbody>
</table>
