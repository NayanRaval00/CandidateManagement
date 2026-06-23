<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <!-- showModal() → top layer + backdrop; close() → dismiss -->
    <button type="button" onclick="document.getElementById('confirm')?.showModal()">
        Delete update…
    </button>

    <dialog id="confirm">
        <p>Remove this item?</p>
        <button type="button" onclick="document.getElementById('confirm')?.close()">
            Cancel
        </button>
        <button type="button" onclick="{
            document.getElementById('confirm')?.close();
            /* your delete work */
            }">
            Delete
        </button>
    </dialog>


    <!-- Trigger -->
    <button type="button" popoverTarget="actions-menu">
        Actions ▾
    </button>

    <!-- Panel (top layer, light-dismiss) -->
    <div id="actions-menu" popover="auto">
        <a href="/item/1"
            popoverTarget="actions-menu"
            popoverTargetAction="hide"
            viewTransition>
            Open item
        </a>
    </div>


</body>

</html>