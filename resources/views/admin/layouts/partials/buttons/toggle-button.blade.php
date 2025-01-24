<!--
    ************************************************
    # Note:
        $class = You any add class/classes as string using this variable.
        $name = "name" field for input element.
        $id = "id" field for input element.
        $dataValue = Accept "1" for checkbox checked otherwise it remain unchecked.
        $toggleBtnText = Text to display for this toggle button.
    ************************************************
-->

<div class="custom-control custom-switch {{ isset($class) && !empty($class) ? $class : '' }}">

    <input type="checkbox" class="custom-control-input" name="{{ isset($name) && !empty($name) ? $name : 'status' }}" id="{{ isset($id) && !empty($id) ? $id : "toggleSwitch" }}" @if(isset($dataValue) && !empty($dataValue) && $dataValue == 1) checked @endif>

    <label class="custom-control-label" for="{{ isset($id) && !empty($id) ? $id : "toggleSwitch" }}">{{ isset($toggleBtnText) && !empty($toggleBtnText) ? $toggleBtnText : 'Yes' }}</label>

</div>
