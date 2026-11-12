<?php

namespace App\Support\Storefront;

/**
 * Script for the page-builder blocks that need interaction.
 *
 * The interactive blocks (carousel, tabs) are plain HTML + CSS + one tiny script, so it works inside the
 * sandboxed CMS page document (which has no Alpine or Tailwind) as well as on
 * the storefront layout.
 */
class PageBlocks
{
    /**
     * The script that powers interactive CMS blocks (carousels, tabs, counters, progress bars, flip boxes, and social share).
     *
     * @param  string  $nonce  CSP nonce for sandboxed documents, empty otherwise.
     * @param  string|null  $shareUrl  Canonical page URL for share buttons when rendered inside a sandboxed document.
     */
    public static function script(string $nonce = '', ?string $shareUrl = null): string
    {
        $encodedShareUrl = json_encode(
            $shareUrl ?? '',
            JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );

        $js = <<<'JS'
function init(root){
    if(root.getAttribute('data-carousel-ready')){return;}
    root.setAttribute('data-carousel-ready','1');
    var slides=[].slice.call(root.querySelectorAll('[data-carousel-slide]'));
    var dots=[].slice.call(root.querySelectorAll('[data-carousel-dot]'));
    if(slides.length<2){return;}
    var current=0,timer=null;
    function show(i){
        current=(i+slides.length)%slides.length;
        slides.forEach(function(s,n){s.classList.toggle('is-active',n===current);});
        dots.forEach(function(d,n){d.classList.toggle('is-active',n===current);});
    }
    function stop(){if(timer){clearInterval(timer);timer=null;}}
    function start(){stop();timer=setInterval(function(){show(current+1);},6000);}
    dots.forEach(function(d,n){d.addEventListener('click',function(){show(n);start();});});
    root.addEventListener('mouseenter',stop);
    root.addEventListener('mouseleave',start);
    show(0);start();
}
function initTabs(root){
    if(root.getAttribute('data-tabs-ready')){return;}
    root.setAttribute('data-tabs-ready','1');
    var buttons=[].slice.call(root.querySelectorAll('[data-tab-btn]'));
    var panels=[].slice.call(root.querySelectorAll('[data-tab-panel]'));
    function open(id){
        buttons.forEach(function(b){
            var active=b.getAttribute('data-tab-btn')===id;
            b.classList.toggle('is-active',active);
            b.setAttribute('aria-selected',active?'true':'false');
        });
        panels.forEach(function(p){p.classList.toggle('is-active',p.getAttribute('data-tab-panel')===id);});
    }
    buttons.forEach(function(b){b.addEventListener('click',function(){open(b.getAttribute('data-tab-btn'));});});
    if(buttons.length){open(buttons[0].getAttribute('data-tab-btn'));}
}
function initFlipBoxes(root){
    if(root.getAttribute('data-flip-ready')){return;}
    root.setAttribute('data-flip-ready','1');
    root.addEventListener('click',function(e){
        if(e.target&&e.target.closest&&e.target.closest('a[href]')){return;}
        root.classList.toggle('is-flipped');
    });
}
function observeOnce(el,cb){
    if('IntersectionObserver' in window){
        var observer=new IntersectionObserver(function(entries){
            entries.forEach(function(entry){
                if(entry.isIntersecting){
                    observer.disconnect();
                    cb();
                }
            });
        },{threshold:0.2});
        observer.observe(el);
    }else{
        cb();
    }
}
function initCounter(el){
    if(el.getAttribute('data-counter-ready')){return;}
    el.setAttribute('data-counter-ready','1');
    var raw=el.getAttribute('data-target');
    var target=parseFloat(raw===null?'':raw);
    if(isNaN(target)){return;}
    var prefix=el.getAttribute('data-prefix')||'';
    var suffix=el.getAttribute('data-suffix')||'';
    var isDecimal=String(raw).indexOf('.')!==-1;
    function format(val){
        var num=isDecimal?Number(val.toFixed(1)).toLocaleString():Math.round(val).toLocaleString();
        return prefix+num+suffix;
    }
    observeOnce(el,function(){
        var duration=1100;
        var startTime=null;
        function step(ts){
            if(startTime===null){startTime=ts;}
            var progress=Math.min(1,(ts-startTime)/duration);
            var eased=1-Math.pow(1-progress,3);
            el.textContent=format(target*eased);
            if(progress<1){requestAnimationFrame(step);}else{el.textContent=format(target);}
        }
        if('requestAnimationFrame' in window){requestAnimationFrame(step);}else{el.textContent=format(target);}
    });
}
function initProgress(el){
    if(el.getAttribute('data-progress-ready')){return;}
    el.setAttribute('data-progress-ready','1');
    var bar=el.querySelector('[data-progress-bar],.hubafrika-progress-fill');
    if(!bar){return;}
    var val=Math.max(0,Math.min(100,parseFloat(el.getAttribute('data-value')||'0')||0));
    bar.style.width='0%';
    observeOnce(el,function(){
        setTimeout(function(){bar.style.width=val+'%';},60);
    });
}
function initShare(){
    var targetUrl=shareUrl||(window.location.protocol.indexOf('http')===0?window.location.href:'');
    var title=document.title||'Hub Afrika';
    var encodedUrl=encodeURIComponent(targetUrl);
    var encodedTitle=encodeURIComponent(title);
    var encodedWa=encodeURIComponent((title?title+' - ':'')+targetUrl);
    [].slice.call(document.querySelectorAll('[data-hubafrika-share]')).forEach(function(link){
        if(link.getAttribute('data-share-ready')){return;}
        link.setAttribute('data-share-ready','1');
        var network=link.getAttribute('data-hubafrika-share');
        var href='';
        if(network==='facebook'){href='https://www.facebook.com/sharer/sharer.php?u='+encodedUrl;}
        else if(network==='twitter'){href='https://twitter.com/intent/tweet?url='+encodedUrl+'&text='+encodedTitle;}
        else if(network==='linkedin'){href='https://www.linkedin.com/sharing/share-offsite/?url='+encodedUrl;}
        else if(network==='whatsapp'){href='https://wa.me/?text='+encodedWa;}
        if(href){
            link.setAttribute('href',href);
            link.setAttribute('target','_blank');
            link.setAttribute('rel','noopener noreferrer');
        }
    });
    [].slice.call(document.querySelectorAll('[data-hubafrika-copy-link]')).forEach(function(btn){
        if(btn.getAttribute('data-copy-ready')){return;}
        btn.setAttribute('data-copy-ready','1');
        btn.addEventListener('click',function(e){
            e.preventDefault();
            if(!targetUrl){return;}
            var label=btn.querySelector('[data-copy-label]')||btn;
            var original=label.textContent;
            function done(){
                label.textContent='Copied!';
                setTimeout(function(){label.textContent=original;},2000);
            }
            if(navigator.clipboard&&navigator.clipboard.writeText){
                navigator.clipboard.writeText(targetUrl).then(done).catch(done);
            }else{
                var input=document.createElement('textarea');
                input.value=targetUrl;
                input.setAttribute('readonly','');
                input.style.position='fixed';
                input.style.opacity='0';
                document.body.appendChild(input);
                input.select();
                try{document.execCommand('copy');}catch(err){}
                document.body.removeChild(input);
                done();
            }
        });
    });
}
function initLogoSlider(root){
    if(root.getAttribute('data-logo-slider-ready')){return;}
    root.setAttribute('data-logo-slider-ready','1');
    var track=root.querySelector('.hubafrika-logo-slider-track,.hubafrika-marquee-track');
    if(!track){return;}
    [].slice.call(track.querySelectorAll('[data-logo-clone]')).forEach(function(node){node.remove();});
    var slides=[].slice.call(track.children);
    if(!slides.length){return;}
    var visible=Math.max(1,Math.min(8,parseInt(root.getAttribute('data-slides-visible')||'6',10)||6));
    var stepConfig=Math.max(1,Math.min(8,parseInt(root.getAttribute('data-slide-step')||'1',10)||1));
    var interval=Math.max(1000,parseInt(root.getAttribute('data-slide-interval')||'3500',10)||3500);
    var loop=root.getAttribute('data-slide-loop')!=='false';
    [].slice.call(root.querySelectorAll('[data-logo-prev],[data-logo-next],.hubafrika-logo-nav')).forEach(function(btn){btn.remove();});
    var count=slides.length;
    var index=0;
    var timer=null;
    var snapTimer=null;
    track.addEventListener('click',function(e){
        var link=e.target&&e.target.closest?e.target.closest('a[data-logo-slide]'):null;
        if(!link){return;}
        var raw=(link.getAttribute('href')||'').trim();
        if(raw===''||raw==='#'){e.preventDefault();}
    });
    function colsCount(){
        var w=window.innerWidth||1200;
        if(w<640){return Math.min(2,visible);}
        if(w<1024){return Math.min(4,visible);}
        return visible;
    }
    function applyLayout(){
        root.style.setProperty('--hubafrika-logo-visible',String(visible));
        root.style.setProperty('--hubafrika-logo-cols',String(Math.min(2,visible)));
        root.style.setProperty('--hubafrika-logo-cols-sm',String(Math.min(4,visible)));
        root.style.setProperty('--hubafrika-logo-cols-lg',String(visible));
    }
    applyLayout();
    if(loop&&count>1){
        var clonesNeeded=Math.min(count,Math.max(visible,stepConfig)+visible);
        for(var c=0;c<clonesNeeded;c++){
            var clone=slides[c].cloneNode(true);
            clone.setAttribute('data-logo-clone','1');
            clone.setAttribute('aria-hidden','true');
            track.appendChild(clone);
        }
    }
    function translate(animated){
        var cols=colsCount();
        track.style.transition=animated?'transform 0.55s cubic-bezier(0.22, 1, 0.36, 1)':'none';
        track.style.transform='translateX(-'+(index*(100/cols))+'%)';
        if(!animated){void track.offsetWidth;}
    }
    function settleLoop(){
        if(snapTimer){clearTimeout(snapTimer);snapTimer=null;}
        if(!loop||index<count){return;}
        snapTimer=setTimeout(function(){
            index=index%count;
            translate(false);
        },580);
    }
    function goNext(){
        var cols=colsCount();
        if(count<=cols){return;}
        var step=Math.min(stepConfig,Math.max(1,count));
        if(!loop){
            var maxIdx=Math.max(0,count-cols);
            index=index>=maxIdx?0:Math.min(maxIdx,index+step);
            translate(true);
            return;
        }
        if(index>=count){index=index%count;translate(false);}
        index+=step;
        translate(true);
        settleLoop();
    }
    function stop(){if(timer){clearInterval(timer);timer=null;}}
    function start(){
        stop();
        if(count<=colsCount()){return;}
        timer=setInterval(goNext,interval);
    }
    root.addEventListener('mouseenter',stop);
    root.addEventListener('mouseleave',start);
    window.addEventListener('resize',function(){
        if(index>=count){index=index%count;}
        translate(false);
    });
    translate(false);
    start();
}
function run(){
    [].slice.call(document.querySelectorAll('[data-hubafrika-carousel]')).forEach(init);
    [].slice.call(document.querySelectorAll('[data-hubafrika-tabs]')).forEach(initTabs);
    [].slice.call(document.querySelectorAll('[data-hubafrika-flip]')).forEach(initFlipBoxes);
    [].slice.call(document.querySelectorAll('[data-hubafrika-counter]')).forEach(initCounter);
    [].slice.call(document.querySelectorAll('[data-hubafrika-progress]')).forEach(initProgress);
    [].slice.call(document.querySelectorAll('[data-hubafrika-logo-slider]')).forEach(initLogoSlider);
    initShare();
}
if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',run);}else{run();}
JS;

        $attribute = $nonce === '' ? '' : ' nonce="'.e($nonce).'"';

        return '<script'.$attribute.'>(function(){var shareUrl='.$encodedShareUrl.';'.$js.'})();</script>';
    }

    /**
     * Populate auto-synced Page Header blocks with the rendered page's title.
     */
    public static function renderPageHeaders(string $html, string $pageTitle): string
    {
        if ($pageTitle === '' || ! str_contains($html, 'data-hubafrika-page-header')) {
            return $html;
        }

        return preg_replace_callback(
            '/<section\b([^>]*\bdata-hubafrika-page-header\b[^>]*)>([\s\S]*?)<\/section>/i',
            static function (array $match) use ($pageTitle): string {
                $attrs = $match[1];
                $body = $match[2];
                $autoTitle = self::attribute($attrs, 'data-auto-title');

                if (! in_array($autoTitle, ['true', '1'], true)) {
                    return $match[0];
                }

                $parent = trim((string) (self::attribute($attrs, 'data-header-parent') ?? ''));
                $safeTitle = e($pageTitle);
                $safeTrail = $parent !== '' ? e($parent).' / '.$safeTitle : $safeTitle;

                $body = preg_replace_callback(
                    '/(<h[1-6]\b[^>]*\bdata-page-header-title\b[^>]*>)[\s\S]*?(<\/h[1-6]>)/i',
                    static fn (array $inner): string => $inner[1].$safeTitle.$inner[2],
                    $body,
                    1,
                ) ?? $body;

                $body = preg_replace_callback(
                    '/(<span\b[^>]*\bdata-page-header-trail\b[^>]*>)[\s\S]*?(<\/span>)/i',
                    static fn (array $inner): string => $inner[1].$safeTrail.$inner[2],
                    $body,
                    1,
                ) ?? $body;

                return '<section'.$attrs.'>'.$body.'</section>';
            },
            $html,
        ) ?? $html;
    }

    /**
     * Read an HTML attribute value from an element's raw attribute string.
     */
    private static function attribute(string $attributes, string $name): ?string
    {
        $pattern = '/\b'.preg_quote($name, '/').'\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))/i';

        if (preg_match($pattern, $attributes, $match) !== 1) {
            return null;
        }

        foreach (array_slice($match, 1) as $value) {
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
