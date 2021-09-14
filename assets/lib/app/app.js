var data = {
    questions: []
}
function removeElement(elem) {
    var index = data.questions.indexOf(elem);
    if (index > -1) {
        data.questions.splice(index, 1);
    }
}

let remove = (id) => { 
    $(`#${id}`).remove()
}
var initPage = {
    request: null,
    Home: () => { 
    },
    Login: () => {
    },
    Tests: () => {
        $('#add-qs').on('click', function() {
            let Q = $("#question-code").val()
            let C = $("#chapter-code").val()
            let M = $("#max-mark").val()
            let c = $('.question').length
            if (notNull([Q, C, M], {string: 0, number: 0})) {
            let q = `
            <div class="question container" id="q${Q}-${C}-${M}-${c}">
                <div class="row">
                    <div class="col valQ">${Q}</div>
                    <div class="col valC" >${C}</div>
                    <div class="col valM">${M}</div> 
                    <div class="col"> <a href='#' onclick='remove("q${Q}-${C}-${M}-${c}")' class='fa-trash fa remove'></a></div>
                    </div>
                    <div> 
                </div>
            </div>`
            
            $('#questions').append(q)
            
            
            }
        })
        $('#save-q').on('click', function() {
            let name = $("#test-name").val()
            let clas = $("#test-class").val()
            if( name.length > 0 ) {
                let data = [];
                $(".question").each((i, item) => {
                    let Q = $(item).find('.row > .valQ').text()
                    let C = $(item).find('.row > .valC').text()
                    let M = $(item).find('.row > .valM').text() 
                    data.push([Q, C, M])
                })
                $.post('', {action: "add-test", name: name, data: data, class:clas }, (response) => {
                    response = JSON.parse(response);
                     if (response.status == 'success') {
                        document.location.reload()
                    } else {
                        alert(response.msg)
                    }
                } )
            } else {
                alert('Please set a test name before adding questions')
            }
        })
    },
    Classes: () => {
        
        $('#save-class').on('click', function() {
            let code    = $("#class-code").val()
            let subject = $("#class-subject").val()
            let size    = $("#class-size").val()
            let chaps   = $("#class-chaps").val()
            let batch   = $("#class-batch").val()
            if( notNull([code, subject, size, chaps, batch], {string: 0, number: 0}) ) {
                $.post('', {action: "add-class", code: code, subject: subject, size: size, chaps: chaps, batch: batch}, (response) => {
                    response = JSON.parse(response);
                    if (response.status == 'success') {
                        document.location.reload()
                    } else {
                        console.log(response)
                        alert("Error!")
                    }
                } )
            } else {
                alert('Please, fill in everything correctly.')
            }
        })
    },
    Class: () => {
        
        $('#add-student').on('click', function() {
            let firstname   = $("#firstname").val()
            let lastname    = $("#lastname").val()
            let email       = $("#email").val()
            let roll        = $("#roll").val()
            let classId     = $("#class").val()
            if( notNull([firstname, lastname, email, roll], {string: 0, number: 0}) ) {
                $.post('', {action: "add-student", class: classId, firstname: firstname, lastname: lastname, email: email, roll: roll}, (response) => {
                    response = JSON.parse(response);
                    if (response.status == 'success') {
                        document.location.reload()
                    } else {
                        console.log(response)
                        alert("Error!")
                    }
                } )
            } else {
                alert('Please, fill in everything correctly.')
            }
        })
    },
    Student: () => {
        $('#add-student-marks').on('click', () => {
            let id = $("#student").val()
            let test = $('#test').val()
            let data = [];
            $(".val").each((i, item) => {
                let q = $(item).data('for')
                let v = $(item).val()
                data.push([q, v])
            })
            $.post('', {action: "add-student-marks", test: test, id: id, data: data}, (response) => {
                response = JSON.parse(response);
                if (response.status == 'success') {
                    document.location.reload()
                } else {
                    alert("Error!")
                }
            } )
        })
        $("#add-test").on('click', function () {
            let name = $('#test').val()
            $("#questions").html('')
            if (name.length > 0)
            $.post('', {action: "get-test", name: name}, (response) => {
                response = JSON.parse(response);
                if (response.status == 'success') { 
                    let qs = response.questions
                    qs.forEach((item) => {
                        $("#questions").append(`
                        <div class="form-group container">
                            <div class='row'>
                                <div class="col"><label for="qx${item.question}">Q.${item.question}</label></div>
                                <div class="col"><input id="qx${item.question}" type="number" class="form-control val" data-for="${item.question}" min="0" max="${item.max_mark}"></div>
                                <div class="col"><input type="text" class="form-control" disabled value="${item.max_mark}"></div>
                            </div>
                        </div>
                    `)
                    })
                    $('#addNew').modal('show')
                } else { 
                }
            })
        })

        $('.updateRef').on('click', function() {
            let id = $(this).data('for')
            let v = $(`#ref-${id}`).val() 
            $.post('', {action: "updateRef", id: id, v: v}, (response) => {
                response = JSON.parse(response); 
                if (response.status == 'success') { 
                    $(`#ref-${id}`).val("")
                    $(`#ref-${id}`).prop("placeholder", v)
                } else {
                    alert("Error!")
                }

            })
        })
        $('.seeResults').on('click', function() {
            let test = $(this).data('test')
            let student = $(this).data('student')
            let date = $(this).data('date') 
            $.post('', {action: "results", test: test, student: student, date: date},  (response) => {
                response = JSON.parse(response); 
                $('#results').html(`<div class="form-group container bg-dark text-light">
                    <div class='row'>
                        <div class="col">Question</div>
                        <div class="col">Chapter</div>
                        <div class="col">Mark</div>
                        <div class="col">Max Mark</div>
                    </div>
                </div>`)
                if (response.status == 'success') { 
                    let rs = response.results
                    $('#resultsDate').html(date)
                    rs.forEach((item) => {
                        let question = item.question.split(" ")
						console.log(question)
						let q = question[0]
						let c = question[1]
                        $("#results").append(`
                        <div class="form-group container">
                            <div class='row'>
                                <div class="col"><label for="qx${item.question}">${q}</label></div>
                                <div class="col"><label>${c}</label></div>
                                <div class="col">${item.mark}</div>
                                <div class="col">${item.max_mark}</div>
                            </div>
                        </div>
                    `)
                    })
                        
                    $("#results").append(`
                    <div class="form-group container bg-dark text-light">
                        <div class='row'>
                            <div class="col">Total</div>
                            <div class="col"></div>
                            <div class="col">${response.total}</div>
                            <div class="col">${response.max_total}</div>
                        </div>
                    </div>
                `)
                    $('#TestModal').modal('show')
                } else { 
                }
            })
        })

    }
}
var actions = { 
    Student: {
            deleteTest: (id, test) => { 
                let c = confirm("Are you sure about deleting the test?")
                if (c) {
                    $.post('', {action: 'delete-student-test', id: id, test: test},  (response) => {
                        response = JSON.parse(response); 
                        if (response.status == 'success') {
                            document.location.reload() 
                        } else {
                            alert("Error!")
                        }
                    } )
                }
            },
            Delete: (id) => {
                let c = confirm("Are you sure about deleting the student?")
                if (c) {
                    $.post('', {action: 'delete-student', id: id},  (response) => {
                        response = JSON.parse(response);
                        if (response.status == 'success') {
                            document.location.reload()
                        } else {
                            alert("Error!")
                        }
                    } )
                }
            }

    },
    Class: {
        Sort: (s, o) => {
            const urlParams = new URLSearchParams(window.location.search);
            const p = urlParams.get('p');
            const id = urlParams.get('id');
            const q = urlParams.get('q');  
            document.location.href = "?p="+p+"&id=" + id + "&q=" + ((q !== null)?q:"") + "&sort=" + s + "&order=" + o
        },
        Search: () => {
            let q = $("#q").val()
            let id = $("#cid").val()
            if (q.length > 0) {
                document.location.href = "?p=Class&id=" + id + "&q=" + q
            }
        },
        Delete: (id) => {
            let c = confirm("Are you sure about deleting the class?")
            if (c) {
                $.post('', {action: 'delete-class', id: id},  (response) => {
                    response = JSON.parse(response);
                    if (response.status == 'success') {
                        document.location.reload()
                    } else {
                        alert("Error!")
                    }
                } )
            }
        }

    },
    Tests: {
        Search: () => {
            let q = $("#q").val()
            if (q.length > 0) {
                document.location.href = "?p=Tests&q=" + q
            }
        }
    },
    Test: {
        Delete: (id) => {
            let c = confirm("Are you sure about deleting the test?")
            if (c) {
                $.post('', {action: 'delete-test', id: id},  (response) => {
                    response = JSON.parse(response);
                    if (response.status == 'success') {
                        document.location.reload()
                    } else {
                        alert("Error!")
                    }
                } )
            }
        }
    },
    User: {
        Change: () => {
            
            let password = $('#current-pw').val()
            let newpw = $('#new-pw').val()
            if (notNull([newpw, password], {string: 0, number: 0})) {
                $.post('', {action: 'change-pw', newpw: newpw, password: password}, function(response) {
                    response = JSON.parse(response);
                    console.log(response)
                    if (response.status == 'success') {
                        document.location.reload()
                    } else {
                        alert("Error!")
                    }
                })
            }
        },
        Logout: () => {
            $.post('', {action: 'logout'}, function() {  document.location.reload() })
        },
        Login: () => {
            let username = $('#username').val()
            let password = $('#password').val()
            if (notNull([username, password], {string: 0, number: 0})) {
                $.post('', {action: 'login', username: username, password: password}, function(response) {
                    response = JSON.parse(response);
                    if (response.status == 'success') {
                        document.location.reload()
                    } else {
                        alert("Wrong credentials, or an inactive account!")
                    }
                })
            }
        },
        Register: () => {
            let username = $('#username').val()
            let password = $('#password').val()
            let fullname = $('#fullname').val()
            let year = $('#year').val()
            if (notNull([username, password, fullname, year], {string: 0, number: 0})) {
                $.post('', {action: 'register', username: username, password: password, fullname: fullname, year: year}, function(response) {
                    response = JSON.parse(response);
                    if (response.status == 'success') {
                        document.location.reload()
                    } else {
                        alert("There was an error: " + response.msg)
                    }
                })
            } else {
                alert("Please fill in everything before submitting!")
            }
        }
    }
}
var showPreloader = () => {
    $('#moreInfo').html("")
    $('#loader').css('visibility', 'visible').fadeIn(150)
    
}
var hidePreloader = () => {
    $('#loader').css('visibility', 'hidden').fadeOut(150)
}
var initAfterPageInit = () => { 

} 
function notNull(v, limits) {
    let vType = typeof v;
    if (vType == "string") 
        if (v.length > limits.string) return true;
    
    if (vType == "number") 
        if (v > limits.number) return true;

    
    if (vType == "object") {
        let okays = 0;
        v.forEach(element => {
            if (typeof element == "string") 
                if (element.length > limits.string) 
                    okays++
            if (typeof element == "number")
                if (element > limits.number)
                    okays++    
        });
        if (v.length == okays) return true;
    }
    return false;
}
 
(function($) {
    $('[data-page]').removeClass('selected')
    $(`[data-page="${window.currentPage}"]`).addClass('selected') 
    $('#pdfLine').on('click', function () {
        let element = document.getElementById('lineChart');
        var opt = {
            margin:       1,
            filename:     'Line.pdf',
            image:        { type: 'jpeg', quality: 1 },
            html2canvas:  { scale: 1 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
          };
          
          html2pdf().set(opt).from(element).save(); 
    })
    $('#pdfPie').on('click', function () {
        let element = document.getElementById('pieChart');
        var opt = {
            margin:       1,
            filename:     'Pie.pdf',
            image:        { type: 'jpeg', quality: 1 },
            html2canvas:  { scale: 1 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
          };
          
          html2pdf().set(opt).from(element).save(); 
    })
})(jQuery)
