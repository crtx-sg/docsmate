
        </main>
    
</section>
<?php if (session()->get('validityMessage')): ?>
    <footer class='website-footer'>
        <p><?= session()->get('validityMessage') ?></p>
    </footer>
<?php endif; ?>
<script>
$(document).ready(function() {

    $('textarea').each(function() {
        var simplemde = new SimpleMDE({
            element: this,
            status: false,
            spellChecker: true,
            // status: [{
            //          className: "characters",
            //          defaultValue: function(el) {
            //             el.innerHTML = "0";
            //          },
            //          onUpdate: function(el) {
            //             el.innerHTML = simplemde.value().length;
            //          }
            //       }],
            showIcons: ["code", "table"],
            hideIcons: ["guide"]
        });
        simplemde.codemirror.refresh();

        simplemde.codemirror.on("update", function(el) {
            const updatedValue = el.getValue()
            var textarea = el.getTextArea();
            textarea.innerHTML = updatedValue;
        });
    });

});

function goBack() {
    window.history.back();
}
</script>

</body>

</html>