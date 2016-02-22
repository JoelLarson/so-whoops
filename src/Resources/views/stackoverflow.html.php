<div class="answers">
    <h2 class="answers-heading">StackOverflow Answers:</h2>
    <?php foreach($answers as $answer): ?>
    <div class="answer">
        <a class="title" href="<?=$answer->link?>"><?=$answer->title?></a>

        <ul class="tags">
            <?php foreach($answer->tags as $tag): ?>
            <li class="tag"><span class="tag-label"><?=$tag?></span></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endforeach; ?>
</div>