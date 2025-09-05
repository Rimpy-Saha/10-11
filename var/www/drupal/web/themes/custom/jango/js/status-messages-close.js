close_status_messages = document.querySelector('#close-status-messages');
if(close_status_messages)
{
close_status_messages.addEventListener('click', function(e){
    e.currentTarget.parentElement.parentElement.remove();
});
}