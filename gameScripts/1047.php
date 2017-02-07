<?php

echo '<script>
useDeskTop.newPane("newMsg");
thisDiv = useDeskTop.getPane("newMsg");

var msgTo = document.createElement("input");
msgTo.className = "msgToBox";
thisDiv.appendChild(msgTo);

var msgSubj = document.createElement("input");
msgSubj.className = "msgSubjBox";
thisDiv.appendChild(msgSubj);

var msgContent = document.createElement("textarea");
msgContent.setAttribute("rows", 20);
msgContent.setAttribute("cols", 20);
thisDiv.appendChild(msgContent);

sendButton = newButton(thisDiv, function () {scrMod("1048,"+ msgTo.value.length + "," + msgSubj.value.length + "," + msgTo.value + msgSubj.value + msgContent.value)});
';

?>