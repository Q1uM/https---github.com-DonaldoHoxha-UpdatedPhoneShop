const sidebarSections = document.querySelectorAll('.sidebar a');

sidebarSections.forEach(section=>{
    section.addEventListener('click', function(event) {
        sidebarSections.forEach(sec => sec.classList.remove('active'));
        this.classList.add('active');

        switch
    });
})

function analytics(){

}

function history(){

}

function users(){

}

function addProduct(){

}

function setting(){

}