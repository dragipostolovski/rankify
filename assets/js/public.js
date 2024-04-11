const cRankifyAnswers = document.querySelectorAll('.c-rankify__answers button');

const handleRankifyRequest = async ( data ) => {
    const response = await fetch( rankify.ajaxurl, {
        method: "post",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
            'X-WP-Nonce': data.ajaxnonce,
        },
        body: new URLSearchParams(data).toString(),
    });

    console.log(response);
    
    if (response.ok) {
        response.json().then( response => {
            console.log(response);
        });
    }
    else {
        console.log('Sorry, we cannot proccess this request.');
    }
}

cRankifyAnswers.forEach( answer => {
    answer.addEventListener('click', () => {
        const post = rankify.post;
        const vote = answer.dataset.answer;

        handleRankifyRequest({
            action: 'rankify_ajax',
            ajaxnonce: rankify.ajaxnonce,
            post: post,
            vote: vote,
            function: 'rankify_vote'
        })
    })
})