<script id="alertmodal-template" type="text/x-handlebars-template">
    <div class="alert alert-{{type}}">
    {{#if text}}
        {{text}}
    {{else}}
    {{#each messages}}
    <ul>
        <li>{{this.[0]}}</li>
    </ul>
    {{/each}}
    {{/if}}
    </div>
</script>