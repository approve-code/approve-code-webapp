$(function () {
    $('.repos').bootstrapSwitch({
        size: 'mini',
        onSwitchChange: toggleRepoState
    });

    function toggleRepoState(event, state) {
        var repositoryId = this.value;
        var route = Routing.generate('ac_reposiory_repository_toggle', {'repository': repositoryId});
        $.post(route);
    }
});
