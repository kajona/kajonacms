
# Statustransition

The statustransition module helps to build status flows for a model. I.e. the most
basic use case is that a model has the following status flow:

![status_flow]

The idea of this module is to have one status handler which contains all informations
about the status flow. Through the status handler we can move the model to the next
state and get a list of available status transitions. So we have one handler object
which can have multiple status options. I.e. to describe the above flow we could use
the following config:

```php
$objHandler = new CustomStatustransitionHandler();

$objHandler->addStatus(new StatustransitionStatus(Model::INT_STATUS_OPEN, "enum_container_status_0", "icon_flag_red"))
    ->addTransition(new StatustransitionTransition(Model::INT_STATUS_IN_REVIEW, Model::STR_STATUS_KEY_OPEN_TO_REVIEW, "enum_container_status_transition_".Model::STR_STATUS_KEY_OPEN_TO_REVIEW, array(
        //objects implements StatustransitionActionInterface
    ), function (Model $objModel) {
        // right check whether the user is allowed to execute the transition
        return true;
    }, array(
        //objects implements StatustransitionConditionInterface
    )));

$objHandler->addStatus(new WorkflowStatus(Model::INT_STATUS_IN_REVIEW, "enum_container_status_2", "icon_flag_yellow"))
    ->addTransition(new WorkflowTransition(Model::INT_STATUS_OPEN, Model::STR_STATUS_KEY_REVIEW_TO_OPEN, "enum_container_status_transition_".Model::STR_STATUS_KEY_REVIEW_TO_OPEN, array(
        //objects implements StatustransitionActionInterface
    ), function (Model $objModel) {
        // right check whether the user is allowed to execute the transition
        return true;
    }))
    ->addTransition(new WorkflowTransition(Model::INT_STATUS_RELEASED, Model::STR_STATUS_KEY_REVIEW_TO_RELEASED, "enum_container_status_transition_".Model::STR_STATUS_KEY_REVIEW_TO_RELEASED, array(
        //objects implements StatustransitionActionInterface
    ), function (Model $objModel) {
        // right check whether the user is allowed to execute the transition
        return true;
    }, array(
        //objects implements StatustransitionConditionInterface
    )));

$objHandler->addStatus(new WorkflowStatus(Model::INT_STATUS_RELEASED, "enum_container_status_1", "icon_flag_green"));
```

In most cases you want to register the handler as a service in the ServiceProvider. Then you
can simply use the handler in the controller through the `@inject` annotation.

The provided `StatustransitionActionInterface` objects are executed if a transition happens.
A status transition is always triggered by a user interaction and not by an automatic event.
The `StatustransitionConditionInterface` objects can validate whether the model has all
required parameters to go into the next state.

[status_flow]: ./status_flow.png

## Database

It is also possible to create a status transition workflow through the UI. To use such a workflow
in your module you simply have to implement the following steps:

* The model has to implement the `StatustransitionFlowChoiceInterface` interface
* The controller needs to use the `StatustransitionControllerTrait` trait

