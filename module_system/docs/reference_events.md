Reference: Events
===

<table>
	<tr>
		<th>Identifier</th>
		<th>Description</th>
	</tr>
	<tr>
		<td>core.system.request.startprocessing</td>
		<td>
			<table>
				<tr><td>Since</td><td>4.6</td></tr>
				<tr><td>Argument</td><td>bool $bitAdmin<br />
			string $strModule<br />
			string $strAction<br />
			string $strLanguageParam</td></tr>				<tr><td>Description</td><td>Invoked right before starting to process the current request. The event is triggered&nbsp;by the request-dispatcher right before the request is given over to the controller.</td></tr>				
			</table>
		</td>
	</tr>
</table>



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
			<td>class_model $objRecord</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>Thrown whenever a record is updated to the database.</td>
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
			<td>Thrown as soon as record is deleted. Listen to those events if you want to trigger additional cleanups or delete linked contents.</td>
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