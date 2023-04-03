@props(['containerKey', 'maxDepth'])
<script src="https://code.jquery.com/jquery-3.6.1.slim.min.js"
    integrity="sha256-w8CvhFs7iHNVUtnSP0YKEg00p9Ih13rlL9zGqvLdePA="
    crossorigin="anonymous"></script>

<script>
    $(document).ready(function () {
        $('#{{ $containerKey }}').nestable({
            group: {{ $containerKey }},
            maxDepth: {{ $maxDepth }},
            expandBtnHTML: '',
            collapseBtnHTML: ''
        });
        $('#nestable-menu [data-action="expand-all"]').on('click', function () {
            $('.dd').nestable('expandAll');
        });
        $('#nestable-menu [data-action="collapse-all"]').on('click', function () {
            $('.dd').nestable('collapseAll');
        });
        $('#nestable-menu [data-action="save"]').on('click', async function (e) {
            let value = $('#{{ $containerKey }}').nestable('serialize');
            let result = await @this.updateTree(value);
            if (result['reload'] === true) {
                console.log('Reload Menu');
                window.location.reload();
            }
        });
    });
</script>