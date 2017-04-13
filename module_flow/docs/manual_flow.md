
# Flow

Through the flow module it is possible to dynamically change the status flow of a model.
By default most models have the following status flow:

![status_flow]

The flow module provides a UI so that the user can adjust the flow according to their needs.

## Implementation

### Controller

The controller needs to use the controller trait which provides the `renderStatusAction` and 
`actionSetStatus` method.

```
use FlowControllerTrait;
```

Note you should not work with hard coded status ints in your code since the index might change.
Instead the code should be extracted to custom actions or conditions which can be attached to a
flow transition.

### Handler

Each module must have a handler class. A basic handler class could look like:

```
<?php

class ModuleStatustransitionHandler extends FlowHandlerAbstract
{
    public function getTitle()
    {
        return "Module name";
    }

    public function getTargetClass()
    {
        return ModuleModel::class;
    }

    public function getAvailableActions()
    {
        return [
        ];
    }

    public function getAvailableConditions()
    {
        return [
        ];
    }
}
```

If the handler class exists you might want to call the `flow_handler_sync` debug script which creates
a flow config for this handler. After this you should see the entry at the UI where you can configure
the flow.

### Rights

It is possible to specify an edit group per status. If the model transitions into this status it
could get the configured user group from the status and change the rights accordingly. This has 
the advantage that the model does not need to overwrite the right* classes which results in better 
performance and it is also possible to check the rights through a SQL query. The model is responsible 
to handle the group rights. The `FlowModelTrait` provides a default `calcPermissions` method implementation 
which can be called inside the `updateStatetToDB` method.


[status_flow]: ./status_flow.png
