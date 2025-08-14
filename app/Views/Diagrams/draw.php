<div class="row p-2">
    <div class="col-12 col-lg-4">

        <div class="card">
            <div class="card-header bg-purple text-light">

                <div class="float-left mt-1">
                    <h4>Code</h4>
                </div>
                <div class="float-right">
                    <select class="selectpicker diagramType">
                        <option value="FLOW_CHART">Flow Chart</option>
                        <option value="SEQUENCE_DIAGRAM">Sequence Diagram</option>                        
                        <option value="CLASS_DIAGRAM">Class Diagram</option>
                        <option value="STATE_DIAGRAM">State Diagram</option>
                        <option value="GANTT_CHART">Gantt Chart</option>
                        <option value="PIE_CHART">Pie Chart</option>
                        <option value="ER_DIAGRAM">ER Diagram</option>
                        <option value="JOURNEY_DIAGRAM">Journey Diagram</option>
                    </select>
                </div>

            </div>
            <div class="card-body">
                <form>
                    <input type="hidden" id="id" name="id" value="" >
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Name</span>
                        </div>

                        <input type="text" class="form-control" name="diagram_name" id="diagram_name" required>
                    </div>
                    
                    <textarea id="mermaidSyntax" name="markdown" class="form-control scroll scroll-purple mt-2" style="height:370px;"></textarea>
                    
                    <?php 
                        if(isset($diagram)){
                            if($diagram['author_id'] == session()->get('id')){
                                $showSubmit = true;
                            }else{
                                $showSubmit = false;
                            }
                        } else{
                            $showSubmit = true;
                        }
                    
                    ?>
                    <?php if($showSubmit) : ?>
                        <div class="mt-2 text-center">
                            <button class="btn btn-primary ">Save</button>
                        </div>
                    <?php endif ?>
                   
                </form>

            </div>
            <div class="image-links card-footer text-center d-none">

                <button class="btn btn-orange ml-2" onclick="copyToClipboard(document.getElementsByClassName('download-link')[0].href)" 
                    data-toggle="popover" data-placement="left" data-content="Copy to clipboard" >
                    <i class="fas fa-clipboard"></i>
                </button>
                <a download href=""  
                    data-toggle="popover" data-placement="right" data-content="Download Image"
                    class="download-link btn btn-purple ml-2" >
                    <i class="fa fa-download"></i>
                </a>
            </div>
        </div>
        <div class="p-1" style="width:100%;right:26px;text-align: right;">
            <a href="https://mermaid-js.github.io/mermaid/" target="_blank" class="">Learn diagram syntax</a>
        </div>
    </div>

    <div class="col-12 col-lg-8 text-center">
        <div class="card">
            <div class="card-header bg-purple text-light ">
                <h4 class=" mt-1">Preview</h4>
            </div>
            <div class="card-body">
                <div class="mermaidImage"  style="min-height:370px;"></div>
            </div>
        </div>

    </div>
</div>



<script>    
    let diagram = new Diagram();
    var svgImage;

    $(document).ready(function () {
        $('[data-toggle="popover"]').popover({trigger: "hover" });

        <?php if (session()->get('success')): ?>
            showFloatingAlert("Success: Diagram created successfully!" ,"bg-success");
        <?php endif; ?>
        
        <?php if(isset($diagram)): ?>
            diagram = <?= json_encode($diagram) ?>;
            $("#id").val(diagram.id);
            $("#diagram_name").val(diagram.diagram_name);
            $("#mermaidSyntax").val(diagram.markdown);
            $('.image-links').removeClass('d-none');
            $('.download-link').attr('href',diagram.link);
            $(".diagramType").addClass('d-none');
        <?php endif ?>

        mermaid.initialize({
            startOnLoad: true,
            flowchart: {
                useMaxWidth: false,
                htmlLabels: false
            }
        });

        if(diagram.markdown == null){
            loadDiagram('FLOW_CHART');
        }else{
            renderImage();
        }

    });

    $(".diagramType").change(function () {
        const type = $(this).val();
        loadDiagram(type);
    })


    $("#mermaidSyntax").on('input', function (e) {
        if (e.target.value != '') {
            renderImage();
        }
    });

    function loadDiagram(type) {
        $("#mermaidSyntax").val(DIAGRAMS[type]);
        renderImage();
    }

    function renderImage() {
        var mermaidSyntax = $("#mermaidSyntax").val();
        try{
            mermaid.render('theGraph', mermaidSyntax, function (svgCode) {
                svgImage = svgCode;
                $(".mermaidImage").html(svgCode);
            });
        }catch(err){
            $("#dtheGraph").appendTo($(".mermaidImage"));
            svgImage = null;
        }

    }

    $('form').on('submit', function (e) {
        e.preventDefault();

        if(svgImage == null){
            showFloatingAlert("Error: Cannot save due to invalid syntax or image!" , "bg-danger");
            return false;
        }
       
        var form = $(this);
        form = form.serializeArray();
        form = form.concat([
            {name: "svg_code", value: svgImage}
        ]);

        makePOSTRequest('/diagrams/save', form)
            .then((response)=>{
                if(response.success == "True"){

                    const diagramId = $("#id").val();
                    diagram = response.diagram;

                    if(diagramId == ""){
                        location.href = "/diagrams/draw?id="+diagram.id;
                    }else{
                        $('.image-links').removeClass('d-none');
                        $('.download-link').attr('href',diagram.link);
                        showFloatingAlert("Success: Diagram updated!" , "bg-success");
                    }
                        
                }else{
                    showFloatingAlert("Error: Something went wrong!" , "bg-danger");
                }
            })
            .catch((err) => {
                console.log(err);
                showPopUp('Error', "An unexpected error occured on server.");
            });
        
    });

    
    let DIAGRAMS = {};

    DIAGRAMS['SEQUENCE_DIAGRAM'] = `sequenceDiagram
        participant Alice
        participant Bob
        Alice->>John: Hello John, how are you?
        loop Healthcheck
            John->>John: Fight against hypochondria
        end
        Note right of John: Rational thoughts <br/>prevail...
        John-->>Alice: Great!
        John->>Bob: How about you?
        Bob-->>John: Jolly good!`;

    DIAGRAMS['FLOW_CHART'] = `graph TD
        A[Christmas] -->|Get money| B(Go shopping)
        B --> C{Let me think}
        C -->|One| D[Laptop]
        C -->|Two| E[iPhone]
        C -->|Three| F[Car]`;

    DIAGRAMS['CLASS_DIAGRAM'] = `classDiagram
        Animal <|-- Duck
        Animal <|-- Fish
        Animal <|-- Zebra
        Animal : +int age
        Animal : +String gender
        Animal: +isMammal()
        Animal: +mate()
        class Duck{
        +String beakColor
        +swim()
        +quack()
        }
        class Fish{
        -int sizeInFeet
        -canEat()
        }
        class Zebra{
        +bool is_wild
        +run()
        }`;

    DIAGRAMS['STATE_DIAGRAM'] = `stateDiagram-v2
        [*] --> Still
        Still --> [*]
        Still --> Moving
        Moving --> Still
        Moving --> Crash
        Crash --> [*]`;

    DIAGRAMS['GANTT_CHART'] = `gantt
        title A Gantt Diagram
        dateFormat  YYYY-MM-DD
        section Section
        A task           :a1, 2020-11-23, 3d
        B task     :after a1  , 2d
        section Another
        C task     :2020-11-24  , 3d
        D task      : 2d`;

    DIAGRAMS['PIE_CHART'] = `pie title Pets adopted by volunteers
        "Dogs" : 386
        "Cats" : 85
        "Rats" : 15`;

    DIAGRAMS['ER_DIAGRAM'] = `erDiagram
        CUSTOMER }|..|{ DELIVERY-ADDRESS : has
        CUSTOMER ||--o{ ORDER : places
        CUSTOMER ||--o{ INVOICE : "liable for"
        DELIVERY-ADDRESS ||--o{ ORDER : receives
        INVOICE ||--|{ ORDER : covers
        ORDER ||--|{ ORDER-ITEM : includes
        PRODUCT-CATEGORY ||--|{ PRODUCT : contains
        PRODUCT ||--o{ ORDER-ITEM : "ordered in"`;

    DIAGRAMS['JOURNEY_DIAGRAM'] = `journey
        title My working day
        section Go to work
        Make tea: 5: Me
        Go upstairs: 3: Me
        Do work: 1: Me, Cat
        section Go home
        Go downstairs: 5: Me
        Sit down: 5: Me`;
</script>