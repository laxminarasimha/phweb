<?php

namespace ShareMyPost;

if(!defined('ABSPATH')){
	exit;
}

class Helpers{

	static function enqueue_frontend_assets(){
		wp_enqueue_style('sharemypost-frontend',SHAREMYPOST_PLUGIN_URL . 'assets/css/frontend.css',[],SHAREMYPOST_VERSION);
		wp_enqueue_script('sharemypost-frontend',SHAREMYPOST_PLUGIN_URL . 'assets/js/frontend.js',['jquery'],SHAREMYPOST_VERSION,true);
		wp_localize_script('sharemypost-frontend', 'sharemypost_ajax', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('sharemypost_frontend_nonce'),
		]);
	}

	static function get_networks() {
		$networks = [
			'twitter' => [
				'label' => __('Twitter/X', 'sharemypost'),
				'url' => 'https://x.com/intent/tweet?url=%s&text=%s',
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>',
			],
			'pinterest' => [
				'label' => __('Pinterest', 'sharemypost'),
				'url'   => 'https://pinterest.com/pin/create/button/?url=%s&description=%s',
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pinterest" viewBox="0 0 16 16"><path d="M8 0a8 8 0 0 0-2.915 15.452c-.07-.633-.134-1.606.027-2.297.146-.625.938-3.977.938-3.977s-.239-.479-.239-1.187c0-1.113.645-1.943 1.448-1.943.682 0 1.012.512 1.012 1.127 0 .686-.437 1.712-.663 2.663-.188.796.4 1.446 1.185 1.446 1.422 0 2.515-1.5 2.515-3.664 0-1.915-1.377-3.254-3.342-3.254-2.276 0-3.612 1.707-3.612 3.471 0 .688.265 1.425.595 1.826a.24.24 0 0 1 .056.23c-.061.252-.196.796-.222.907-.035.146-.116.177-.268.107-1-.465-1.624-1.926-1.624-3.1 0-2.523 1.834-4.84 5.286-4.84 2.775 0 4.932 1.977 4.932 4.62 0 2.757-1.739 4.976-4.151 4.976-.811 0-1.573-.421-1.834-.919l-.498 1.902c-.181.695-.669 1.566-.995 2.097A8 8 0 1 0 8 0"/></svg>',
			],
			'reddit' => [
				'label' => __('Reddit', 'sharemypost'),
				'url' => 'https://reddit.com/submit?url=%s&title=%s',
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-reddit" viewBox="0 0 16 16"><path d="M6.167 8a.83.83 0 0 0-.83.83c0 .459.372.84.83.831a.831.831 0 0 0 0-1.661m1.843 3.647c.315 0 1.403-.038 1.976-.611a.23.23 0 0 0 0-.306.213.213 0 0 0-.306 0c-.353.363-1.126.487-1.67.487-.545 0-1.308-.124-1.671-.487a.213.213 0 0 0-.306 0 .213.213 0 0 0 0 .306c.564.563 1.652.61 1.977.61zm.992-2.807c0 .458.373.83.831.83s.83-.381.83-.83a.831.831 0 0 0-1.66 0z"/><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.828-1.165c-.315 0-.602.124-.812.325-.801-.573-1.9-.945-3.121-.993l.534-2.501 1.738.372a.83.83 0 1 0 .83-.869.83.83 0 0 0-.744.468l-1.938-.41a.2.2 0 0 0-.153.028.2.2 0 0 0-.086.134l-.592 2.788c-1.24.038-2.358.41-3.17.992-.21-.2-.496-.324-.81-.324a1.163 1.163 0 0 0-.478 2.224q-.03.17-.029.353c0 1.795 2.091 3.256 4.669 3.256s4.668-1.451 4.668-3.256c0-.114-.01-.238-.029-.353.401-.181.688-.592.688-1.069 0-.65-.525-1.165-1.165-1.165"/></svg>',
			],
			'mastodon' => [
				'label' => __('Mastodon', 'sharemypost'),
				'url' => 'https://mastodon.social/share?text=%s',
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-mastodon" viewBox="0 0 16 16"><path d="M11.19 12.195c2.016-.24 3.77-1.475 3.99-2.603.348-1.778.32-4.339.32-4.339 0-3.47-2.286-4.488-2.286-4.488C12.062.238 10.083.017 8.027 0h-.05C5.92.017 3.942.238 2.79.765c0 0-2.285 1.017-2.285 4.488l-.002.662c-.004.64-.007 1.35.011 2.091.083 3.394.626 6.74 3.78 7.57 1.454.383 2.703.463 3.709.408 1.823-.1 2.847-.647 2.847-.647l-.06-1.317s-1.303.41-2.767.36c-1.45-.05-2.98-.156-3.215-1.928a4 4 0 0 1-.033-.496s1.424.346 3.228.428c1.103.05 2.137-.064 3.188-.189zm1.613-2.47H11.13v-4.08c0-.859-.364-1.295-1.091-1.295-.804 0-1.207.517-1.207 1.541v2.233H7.168V5.89c0-1.024-.403-1.541-1.207-1.541-.727 0-1.091.436-1.091 1.296v4.079H3.197V5.522q0-1.288.66-2.046c.456-.505 1.052-.764 1.793-.764.856 0 1.504.328 1.933.983L8 4.39l.417-.695c.429-.655 1.077-.983 1.934-.983.74 0 1.336.259 1.791.764q.662.757.661 2.046z"/></svg>',
			],
			'threads' => [
				'label' => __('Threads', 'sharemypost'),
				'url'   => 'https://www.threads.net/intent/post?text=%s',
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-threads-fill" viewBox="0 0 16 16"><path d="M6.81 9.204c0-.41.197-1.062 1.727-1.062.469 0 .758.034 1.146.121-.124 1.606-.91 1.818-1.674 1.818-.418 0-1.2-.218-1.2-.877Z"/><path d="M2.59 16h10.82A2.59 2.59 0 0 0 16 13.41V2.59A2.59 2.59 0 0 0 13.41 0H2.59A2.59 2.59 0 0 0 0 2.59v10.82A2.59 2.59 0 0 0 2.59 16M5.866 5.91c.567-.81 1.315-1.126 2.35-1.126.73 0 1.351.246 1.795.711.443.466.696 1.132.754 1.983q.368.154.678.363c.832.559 1.29 1.395 1.29 2.353 0 2.037-1.67 3.806-4.692 3.806-2.595 0-5.291-1.51-5.291-6.004C2.75 3.526 5.361 2 8.033 2c1.234 0 4.129.182 5.217 3.777l-1.02.264c-.842-2.56-2.607-2.968-4.224-2.968-2.675 0-4.187 1.628-4.187 5.093 0 3.107 1.69 4.757 4.222 4.757 2.083 0 3.636-1.082 3.636-2.667 0-1.079-.906-1.595-.953-1.595-.177.925-.651 2.482-2.733 2.482-1.213 0-2.26-.838-2.26-1.936 0-1.568 1.488-2.136 2.663-2.136.44 0 .97.03 1.247.086 0-.478-.404-1.296-1.426-1.296-.911 0-1.16.288-1.45.624l-.024.027c-.202-.135-.875-.601-.875-.601Z"/></svg>',
			],
			'email' => [
				'label' => __('Email', 'sharemypost'),
				'url' => 'mailto:?subject=%s&body=%s',
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16"><path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/></svg>',
			],
			'print' => [
				'label' => __('Print', 'sharemypost'),
				'url' => '#',
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16"><path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1"/><path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/></svg>',
			],

			'flipboard' => [
				'label' => __('Flipboard', 'sharemypost'),
				'url' => 'https://share.flipboard.com/bookmarklet/popout?v=2&url=%s',
				'icon' => '<svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Flipboard</title><path d="M0 0v24h24V0H0zm19.2 9.6h-4.8v4.8H9.6v4.8H4.8V4.8h14.4v4.8z"/></svg>',
			],
			'hackernews' => [
				'label' => __('Hacker News', 'sharemypost'),
				'url' => 'https://news.ycombinator.com/submitlink?u=%s&t=%s',
				'icon' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 11.23l4.08-7.85h2.33l-5.32 9.68v6.52h-2.18V13.06L5.59 3.38h2.46z" fill="currentColor"/></svg>',
			],
			'line' => [
				'label' => __('Line', 'sharemypost'),
				'url' => 'https://social-plugins.line.me/web/share?url=%s',
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-line" viewBox="0 0 16 16"><path d="M8 0c4.411 0 8 2.912 8 6.492 0 1.433-.555 2.723-1.715 3.994-1.678 1.932-5.431 4.285-6.285 4.645-.83.35-.734-.197-.696-.413l.003-.018.114-.685c.027-.204.055-.521-.026-.723-.09-.223-.444-.339-.704-.395C2.846 12.39 0 9.701 0 6.492 0 2.912 3.59 0 8 0M5.022 7.686H3.497V4.918a.156.156 0 0 0-.155-.156H2.78a.156.156 0 0 0-.156.156v3.486c0 .041.017.08.044.107v.001l.002.002.002.002a.15.15 0 0 0 .108.043h2.242c.086 0 .155-.07.155-.156v-.56a.156.156 0 0 0-.155-.157m.791-2.924a.156.156 0 0 0-.156.156v3.486c0 .086.07.155.156.155h.562c.086 0 .155-.07.155-.155V4.918a.156.156 0 0 0-.155-.156zm3.863 0a.156.156 0 0 0-.156.156v2.07L7.923 4.832l-.013-.015v-.001l-.01-.01-.003-.003-.011-.009h-.001L7.88 4.79l-.003-.002-.005-.003-.008-.005h-.002l-.003-.002-.01-.004-.004-.002-.01-.003h-.002l-.003-.001-.009-.002h-.006l-.003-.001h-.004l-.002-.001h-.574a.156.156 0 0 0-.156.155v3.486c0 .086.07.155.156.155h.56c.087 0 .157-.07.157-.155v-2.07l1.6 2.16a.2.2 0 0 0 .039.038l.001.001.01.006.004.002.008.004.007.003.005.002.01.003h.003a.2.2 0 0 0 .04.006h.56c.087 0 .157-.07.157-.155V4.918a.156.156 0 0 0-.156-.156zm3.815.717v-.56a.156.156 0 0 0-.155-.157h-2.242a.16.16 0 0 0-.108.044h-.001l-.001.002-.002.003a.16.16 0 0 0-.044.107v3.486c0 .041.017.08.044.107l.002.003.002.002a.16.16 0 0 0 .108.043h2.242c.086 0 .155-.07.155-.156v-.56a.156.156 0 0 0-.155-.157H11.81v-.589h1.525c.086 0 .155-.07.155-.156v-.56a.156.156 0 0 0-.155-.157H11.81v-.589h1.525c.086 0 .155-.07.155-.156Z"/></svg>',
			],
			'wechat' => [
				'label' => __('WeChat', 'sharemypost'),
				'url' => 'weixin://dl/moments?text=%s',
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-wechat" viewBox="0 0 16 16"><path d="M11.176 14.429c-2.665 0-4.826-1.8-4.826-4.018 0-2.22 2.159-4.02 4.824-4.02S16 8.191 16 10.411c0 1.21-.65 2.301-1.666 3.036a.32.32 0 0 0-.12.366l.218.81a.6.6 0 0 1 .029.117.166.166 0 0 1-.162.162.2.2 0 0 1-.092-.03l-1.057-.61a.5.5 0 0 0-.256-.074.5.5 0 0 0-.142.021 5.7 5.7 0 0 1-1.576.22M9.064 9.542a.647.647 0 1 0 .557-1 .645.645 0 0 0-.646.647.6.6 0 0 0 .09.353Zm3.232.001a.646.646 0 1 0 .546-1 .645.645 0 0 0-.644.644.63.63 0 0 0 .098.356"/><path d="M0 6.826c0 1.455.781 2.765 2.001 3.656a.385.385 0 0 1 .143.439l-.161.6-.1.373a.5.5 0 0 0-.032.14.19.19 0 0 0 .193.193q.06 0 .111-.029l1.268-.733a.6.6 0 0 1 .308-.088q.088 0 .171.025a6.8 6.8 0 0 0 1.625.26 4.5 4.5 0 0 1-.177-1.251c0-2.936 2.785-5.02 5.824-5.02l.15.002C10.587 3.429 8.392 2 5.796 2 2.596 2 0 4.16 0 6.826m4.632-1.555a.77.77 0 1 1-1.54 0 .77.77 0 0 1 1.54 0m3.875 0a.77.77 0 1 1-1.54 0 .77.77 0 0 1 1.54 0"/></svg>',
			],
			'snapchat' => [
				'label' => __('Snapchat', 'sharemypost'),
				'url' => 'https://snapchat.com/scan?attachmentUrl=%s',
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-snapchat" viewBox="0 0 16 16"><path d="M15.943 11.526c-.111-.303-.323-.465-.564-.599a1 1 0 0 0-.123-.064l-.219-.111c-.752-.399-1.339-.902-1.746-1.498a3.4 3.4 0 0 1-.3-.531c-.034-.1-.032-.156-.008-.207a.3.3 0 0 1 .097-.1c.129-.086.262-.173.352-.231.162-.104.289-.187.371-.245.309-.216.525-.446.66-.702a1.4 1.4 0 0 0 .069-1.16c-.205-.538-.713-.872-1.329-.872a1.8 1.8 0 0 0-.487.065c.006-.368-.002-.757-.035-1.139-.116-1.344-.587-2.048-1.077-2.61a4.3 4.3 0 0 0-1.095-.881C9.764.216 8.92 0 7.999 0s-1.76.216-2.505.641c-.412.232-.782.53-1.097.883-.49.562-.96 1.267-1.077 2.61-.033.382-.04.772-.036 1.138a1.8 1.8 0 0 0-.487-.065c-.615 0-1.124.335-1.328.873a1.4 1.4 0 0 0 .067 1.161c.136.256.352.486.66.701.082.058.21.14.371.246l.339.221a.4.4 0 0 1 .109.11c.026.053.027.11-.012.217a3.4 3.4 0 0 1-.295.52c-.398.583-.968 1.077-1.696 1.472-.385.204-.786.34-.955.8-.128.348-.044.743.28 1.075q.18.189.409.31a4.4 4.4 0 0 0 1 .4.7.7 0 0 1 .202.09c.118.104.102.26.259.488q.12.178.296.3c.33.229.701.243 1.095.258.355.014.758.03 1.217.18.19.064.389.186.618.328.55.338 1.305.802 2.566.802 1.262 0 2.02-.466 2.576-.806.227-.14.424-.26.609-.321.46-.152.863-.168 1.218-.181.393-.015.764-.03 1.095-.258a1.14 1.14 0 0 0 .336-.368c.114-.192.11-.327.217-.42a.6.6 0 0 1 .19-.087 4.5 4.5 0 0 0 1.014-.404c.16-.087.306-.2.429-.336l.004-.005c.304-.325.38-.709.256-1.047m-1.121.602c-.684.378-1.139.337-1.493.565-.3.193-.122.61-.34.76-.269.186-1.061-.012-2.085.326-.845.279-1.384 1.082-2.903 1.082s-2.045-.801-2.904-1.084c-1.022-.338-1.816-.14-2.084-.325-.218-.15-.041-.568-.341-.761-.354-.228-.809-.187-1.492-.563-.436-.24-.189-.39-.044-.46 2.478-1.199 2.873-3.05 2.89-3.188.022-.166.045-.297-.138-.466-.177-.164-.962-.65-1.18-.802-.36-.252-.52-.503-.402-.812.082-.214.281-.295.49-.295a1 1 0 0 1 .197.022c.396.086.78.285 1.002.338q.04.01.082.011c.118 0 .16-.06.152-.195-.026-.433-.087-1.277-.019-2.066.094-1.084.444-1.622.859-2.097.2-.229 1.137-1.22 2.93-1.22 1.792 0 2.732.987 2.931 1.215.416.475.766 1.013.859 2.098.068.788.009 1.632-.019 2.065-.01.142.034.195.152.195a.4.4 0 0 0 .082-.01c.222-.054.607-.253 1.002-.338a1 1 0 0 1 .197-.023c.21 0 .409.082.49.295.117.309-.04.56-.401.812-.218.152-1.003.638-1.18.802-.184.169-.16.3-.139.466.018.14.413 1.991 2.89 3.189.147.073.394.222-.041.464"/></svg>',
			],
			'mix' => [
				'label' => __('Mix', 'sharemypost'),
				'url' => 'https://mix.com/save?url=%s',
				'icon' => '<svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Mix</title><path d="M.001 0v21.61c0 1.32 1.074 2.39 2.4 2.39a2.396 2.396 0 0 0 2.402-2.39V8.54c0 .014-.005.026-.006.04V6.364a2.395 2.395 0 0 1 2.399-2.393 2.396 2.396 0 0 1 2.398 2.393v9.356a2.394 2.394 0 0 0 2.398 2.393 2.394 2.394 0 0 0 2.398-2.39v-3.692a2.398 2.398 0 0 1 2.385-2.078 2.4 2.4 0 0 1 2.41 2.389v1.214a2.397 2.397 0 0 0 2.408 2.389 2.399 2.399 0 0 0 2.406-2.39V.006a4.61 4.61 0 0 0-.145-.004c-1.31 0-2.558.264-3.693.74A9.449 9.449 0 0 1 23.841 0z"/></svg>',
			],
			'pocket' => [
				'label' => __('Pocket', 'sharemypost'),
				'url' => 'https://getpocket.com/save?url=%s',
					'icon' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.01 2.3A2.8 2.8 0 0 0 20 1.25H4A2.8 2.8 0 0 0 2 2.3c-.62.61-.95 1.44-.94 2.32v6.6a11 11 0 0 0 22 0v-6.6a2.8 2.8 0 0 0-.94-2.32zM12 16.73a4.7 4.7 0 0 1-3.32-1.37l-4.52-4.5a1.26 1.26 0 0 1 1.78-1.78l4.52 4.51a1.9 1.9 0 0 0 2.68 0l4.52-4.51a1.26 1.26 0 0 1 1.78 1.78l-4.52 4.5A4.7 4.7 0 0 1 12 16.73z" fill="currentColor"/></svg>',
			],
			'sms' => [
				'label' => __('SMS', 'sharemypost'),
				'url' => 'sms:?body=%s',
				'icon' => '<svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Google Messages</title><path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zM4.911 7.089h11.456a2.197 2.197 0 0 1 2.165 2.19v5.863a2.213 2.213 0 0 1-2.177 2.178H8.04c-1.174 0-2.04-.99-2.04-2.178v-4.639L4.503 7.905c-.31-.42-.05-.816.408-.816zm3.415 2.19c-.347 0-.68.21-.68.544 0 .334.333.544.68.544h7.905c.346 0 .68-.21.68-.544 0-.334-.334-.545-.68-.545zm0 2.177c-.347 0-.68.21-.68.544 0 .334.333.544.68.544h7.905c.346 0 .68-.21.68-.544 0-.334-.334-.544-.68-.544zm-.013 2.19c-.346 0-.68.21-.68.544 0 .334.334.544.68.544h5.728c.347 0 .68-.21.68-.544 0-.334-.333-.545-.68-.545z"/></svg>',
			],
			'tumblr' => [
				'label' => __('Tumblr', 'sharemypost'),
				'url' => 'https://www.tumblr.com/widgets/share/tool?posttype=link&tags=blog&caption=&content=%s',
				'icon' => '<svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Tumblr</title><path d="M14.563 24c-5.093 0-7.031-3.756-7.031-6.411V9.747H5.116V6.648c3.63-1.313 4.512-4.596 4.71-6.469C9.84.051 9.941 0 9.999 0h3.517v6.114h4.801v3.633h-4.82v7.47c.016 1.001.375 2.371 2.207 2.371h.09c.631-.02 1.486-.205 1.936-.419l1.156 3.425c-.436.636-2.4 1.374-4.156 1.404h-.178l.011.002z"/></svg>',
			],
			'xing' => [
				'label' => __('XING', 'sharemypost'),
				'url'   => 'https://www.xing.com/app/user?op=share;url=%s',
				'icon'  => '<svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Xing</title><path d="M18.188 0c-.517 0-.741.325-.927.66 0 0-7.455 13.224-7.702 13.657.015.024 4.919 9.023 4.919 9.023.17.308.436.66.967.66h3.454c.211 0 .375-.078.463-.22.089-.151.089-.346-.009-.536l-4.879-8.916c-.004-.006-.004-.016 0-.022L22.139.756c.095-.191.097-.387.006-.535C22.056.078 21.894 0 21.686 0h-3.498zM3.648 4.74c-.211 0-.385.074-.473.216-.09.149-.078.339.02.531l2.34 4.05c.004.01.004.016 0 .021L1.86 16.051c-.099.188-.093.381 0 .529.085.142.239.234.45.234h3.461c.518 0 .766-.348.945-.667l3.734-6.609-2.378-4.155c-.172-.315-.434-.659-.962-.659H3.648v.016z"/></svg>',
			],
			'vk' => [
				'label' => __('VKontakte', 'sharemypost'),
				'url' => 'https://vk.com/share.php?url=%s',
				'icon' => '<svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>VK</title><path d="m9.489.004.729-.003h3.564l.73.003.914.01.433.007.418.011.403.014.388.016.374.021.36.025.345.03.333.033c1.74.196 2.933.616 3.833 1.516.9.9 1.32 2.092 1.516 3.833l.034.333.029.346.025.36.02.373.025.588.012.41.013.644.009.915.004.98-.001 3.313-.003.73-.01.914-.007.433-.011.418-.014.403-.016.388-.021.374-.025.36-.03.345-.033.333c-.196 1.74-.616 2.933-1.516 3.833-.9.9-2.092 1.32-3.833 1.516l-.333.034-.346.029-.36.025-.373.02-.588.025-.41.012-.644.013-.915.009-.98.004-3.313-.001-.73-.003-.914-.01-.433-.007-.418-.011-.403-.014-.388-.016-.374-.021-.36-.025-.345-.03-.333-.033c-1.74-.196-2.933-.616-3.833-1.516-.9-.9-1.32-2.092-1.516-3.833l-.034-.333-.029-.346-.025-.36-.02-.373-.025-.588-.012-.41-.013-.644-.009-.915-.004-.98.001-3.313.003-.73.01-.914.007-.433.011-.418.014-.403.016-.388.021-.374.025-.36.03-.345.033-.333c.196-1.74.616-2.933 1.516-3.833.9-.9 2.092-1.32 3.833-1.516l.333-.034.346-.029.36-.025.373-.02.588-.025.41-.012.644-.013.915-.009ZM6.79 7.3H4.05c.13 6.24 3.25 9.99 8.72 9.99h.31v-3.57c2.01.2 3.53 1.67 4.14 3.57h2.84c-.78-2.84-2.83-4.41-4.11-5.01 1.28-.74 3.08-2.54 3.51-4.98h-2.58c-.56 1.98-2.22 3.78-3.8 3.95V7.3H10.5v6.92c-1.6-.4-3.62-2.34-3.71-6.92Z"/></svg>',
			],
			'buffer' => [
				'label' => __('Buffer', 'sharemypost'),
				'url' => 'https://buffer.com/add?url=%s&text=%s',
				'icon' => '<svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Buffer</title><path d="M1.371 5.476L11.943 0l10.686 5.476-10.686 5.495zm3.36 4.81l7.212 3.547 7.288-3.547 3.398 1.655-10.686 5.202L1.371 11.94zm0 6.171l7.212 3.911 7.288-3.91 3.398 1.815L11.943 24 1.371 18.273z"/></svg>',
			],
			'yummly' => [
				'label' => __('Yummly', 'sharemypost'),
				'url'   => 'https://www.yummly.com/urb/verify-recipe?url=%s',
				'icon'  => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="24" height="24" aria-hidden="true" style="width:24px!important;height:24px!important;"><path d="M7 2C5.9 2 5 2.9 5 4V8C5 9.48 5.81 10.77 7 11.46V22H9V11.46C10.19 10.77 11 9.48 11 8V4C11 2.9 10.1 2 9 2V8H8V2H7ZM16 2C14.9 2 14 2.9 14 4V11C14 12.1 14.9 13 16 13H17V22H19V2H16Z" fill="currentColor"/></svg>',
			],
			'nextdoor' => [
				'label' => __('Nextdoor', 'sharemypost'),
				'url'   => 'https://nextdoor.com/api/validate-share/?url=%s',
				'icon'  => '<svg viewBox="0 0 32 32" width="32" height="32" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M16 3L4 12h3v14h7v-8h4v8h7V12h3L16 3z"/></svg>',
			],
			'gab' => [
				'label' => __('Gab', 'sharemypost'),
				'url'   => 'https://gab.com/compose?url=%s',
				'icon'  => '<svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Grocy</title><path d="M12.621.068C7.527.786 3.608 4.618 2.345 10.082c-.316 1.35-.392 3.896-.163 5.203.62 3.57 2.96 6.574 6.15 7.913 1.36.577 2.1.73 3.842.784 1.22.043 1.862.01 2.722-.13 2.688-.447 5.399-1.699 6.65-3.092l.403-.447-.054-1.872a481.92 481.92 0 0 1-.12-5.344l-.065-3.473-2.907.087c-1.589.033-3.722.098-4.746.142l-1.85.065-.087 2.319c-.055 1.284-.076 2.34-.055 2.362.022.022.882.076 1.916.12l1.872.076v.294c0 .707-.13.98-.555 1.208-.653.326-1.872.479-2.623.326-2.71-.566-3.777-4.55-1.96-7.369C11.86 7.48 13.873 6.62 16.562 6.74c.74.043 1.665.163 2.123.272.446.12.838.174.87.12.098-.142.468-5.726.403-5.9-.087-.24-1.35-.697-2.569-.947-1.252-.25-3.722-.37-4.767-.218z"/></svg>',
			],
		];

		$networks = apply_filters('sharemypost_networks', $networks);
		return $networks;

	}

	static function get_enabled_networks($type = 'global'){

		$settings = \ShareMyPost\Util::get_settings();

		$key_map = apply_filters('sharemypost_enabled_networks_key', [
			'inline' => 'inline_enabled_networks',
		], $type);

		if(isset($key_map[$type])){
			$enabled = (array) (isset($settings[$key_map[$type]]) ? $settings[$key_map[$type]] : []);
		}else{
			$enabled = (array) (isset($settings['enabled_networks']) ? $settings['enabled_networks'] : []);
		}

		$enabled = array_map('sanitize_key', (array) $enabled);

		return array_intersect_key(
			self::get_networks(),
			array_flip($enabled)
		);
	}

	
	static function normalize_network_order($order_input) {
		if(is_string($order_input)){
			$order_input = array_map('trim', explode(',', $order_input));
		}

		if(!is_array($order_input)){
			return [];
		}

		$sanitized = array_values(array_unique(array_map('sanitize_key', $order_input)));
		$valid = array_keys(self::get_networks());
		$order = array_values(array_intersect($sanitized, $valid));
		return $order;
	}

	static function get_networks_ordered($type = 'global'){
		$defaults = \ShareMyPost\Util::get_default_settings();
		$settings = \ShareMyPost\Util::get_settings();
		$all_networks = self::get_networks();

		$order_map = apply_filters('sharemypost_network_order_key', [
			'inline' => 'inline_network_order',
		], $type);

		if(isset($order_map[$type])){
			$key = $order_map[$type];
			$order = isset($settings[$key]) ? self::normalize_network_order($settings[$key]) : [];
			if(empty($order) && isset($defaults[$key])) {
				$order = $defaults[$key];
			}
		}else{
			$order = self::normalize_network_order($settings['network_order']);
			if(empty($order)) {
				$order = $defaults['network_order'];
			}
		}

		if($type === 'inline'){
			$all_networks = apply_filters('sharemypost_inline_networks_list', $all_networks);
		}

		$ordered = [];
		foreach($order as $network_key){
			if(isset($all_networks[$network_key])) {
				$ordered[$network_key] = $all_networks[$network_key];
			}
		}

		foreach($all_networks as $network_key => $data){
			if(!isset($ordered[$network_key])){
				$ordered[$network_key] = $data;
			}
		}

		return $ordered;
	}

	static function sanitize_settings($input){

		$defaults = \ShareMyPost\Util::get_default_settings();
		$current = get_option('sharemypost_settings', []);

		$section = '';
		if (isset($input['section'])) {
			$section = sanitize_key($input['section']);
		}

		$is_inline_tab = ($section !== '') ? ($section === 'inline') : isset($input['inline_position']);

		$checkbox_arrays = [
			'inline_post_types',
			'inline_enabled_networks',
		];

		foreach($checkbox_arrays as $key){
			$is_set_in_form = isset($input[$key]);

			if(!$is_set_in_form){
				if($is_inline_tab && in_array($key, ['inline_post_types','inline_enabled_networks'], true)){
					$input[$key] = [];
				}else{
					$input[$key] = isset($current[$key]) ? $current[$key] : (isset($defaults[$key]) ? $defaults[$key] : []);
				}
			}
		}

		$single_checkboxes = [
			'inline_enabled',
			'mobile',
		];

		foreach($single_checkboxes as $key){
			$is_set_in_form = isset($input[$key]);

			if(!$is_set_in_form){
				if($is_inline_tab && ($key === 'mobile' || strpos($key, 'inline_') === 0)){
					$input[$key] = 0;
				}else{
					$input[$key] = isset($current[$key]) ? $current[$key] : (isset($defaults[$key]) ? $defaults[$key] : 0);
				}
			}
		}

		$input = wp_parse_args((array)$input, wp_parse_args($current, $defaults));

		$input['inline_enabled'] = !empty($input['inline_enabled']) ? 1 : 0;
		$input['mobile'] = !empty($input['mobile']) ? 1 : 0;

		$inline_position = isset($input['inline_position']) ? wp_unslash($input['inline_position']) : '';
		$input['inline_position'] = in_array($inline_position, ['above', 'below', 'both'], true) ? $inline_position : $defaults['inline_position'];

		$raw_post_types = (array)(isset($input['inline_post_types']) ? $input['inline_post_types'] : []);
		$raw_post_types = wp_unslash($raw_post_types);
		$input['inline_post_types'] = array_values(array_filter(array_map('sanitize_key', $raw_post_types)));

		$inline_show_labels = isset($input['inline_show_labels']) ? wp_unslash($input['inline_show_labels']) : '';
		$input['inline_show_labels'] = in_array($inline_show_labels, ['icon_only', 'label_only', 'both'], true) ? $inline_show_labels : $defaults['inline_show_labels'];

		$inline_button_color_type = isset($input['inline_button_color_type']) ? wp_unslash($input['inline_button_color_type']) : '';
		$input['inline_button_color_type'] = in_array($inline_button_color_type, ['original', 'custom'], true) ? $inline_button_color_type : $defaults['inline_button_color_type'];

		$inline_button_color = isset($input['inline_button_color']) ? wp_unslash($input['inline_button_color']) : '';
		$input['inline_button_color'] = sanitize_hex_color($inline_button_color);
		if(empty($input['inline_button_color'])){
			$input['inline_button_color'] = $defaults['inline_button_color'];
		}

		$networks = array_keys(\ShareMyPost\Helpers::get_networks());

		$inline_enabled_networks = (array)(isset($input['inline_enabled_networks']) ? $input['inline_enabled_networks'] : []);
		$inline_enabled_networks = wp_unslash($inline_enabled_networks);
		$input['inline_enabled_networks'] = array_values(array_intersect($networks, $inline_enabled_networks));

		if(isset($input['inline_network_order'])){
			$order = self::normalize_network_order(wp_unslash($input['inline_network_order']));
			$input['inline_network_order'] = !empty($order) ? $order : $defaults['inline_network_order'];
		}else{
			$input['inline_network_order'] = $defaults['inline_network_order'];
		}

		$container_width = isset($input['container_width']) ? wp_unslash($input['container_width']) : '';
		$input['container_width'] = in_array($container_width, ['auto', 'full'], true) ? $container_width : $defaults['container_width'];

		return apply_filters('sharemypost_sanitize_settings', $input, $defaults, $current);
	}

	static function build_share_url($network, $post_id, $type = 'global') {

        $networks = self::get_networks();
        if(!isset($networks[$network])){
            return '';
        }

		$url_template = $networks[$network]['url'];

		$post      = get_post($post_id);
		$permalink = get_permalink($post_id);
		
	$settings = \ShareMyPost\Util::get_settings();
	$permalink = apply_filters('sharemypost_share_url_permalink', $permalink, $post, $network, $settings);
        
	switch($network){
            case 'buffer':
            case 'twitter':
            case 'pinterest':
            case 'reddit':
            case 'telegram':
            case 'hackernews':
            case 'email':
                return sprintf($url_template, rawurlencode($permalink), rawurlencode($post->post_title));
            case 'whatsapp':
            case 'sms':
                return sprintf($url_template, rawurlencode($post->post_title . ' ' . $permalink));
            case 'facebook':
            case 'linkedin':
            case 'mastodon':
            case 'bluesky':
            case 'yummly':
            case 'nextdoor':
            case 'line':
            case 'pocket':
            case 'tumblr':
            case 'vk':
            case 'xing':
            case 'mix':
            case 'flipboard':
            case 'messenger':
            case 'print':
                return sprintf($url_template, rawurlencode($permalink));
            default:
                return sprintf($url_template, rawurlencode($permalink));
        }
	}

	static function build_share_buttons($post_id, $type = 'global') {
		$enabled = array_keys(self::get_enabled_networks($type));
		$ordered_networks = self::get_networks_ordered($type);
		$buttons = [];

		foreach ($ordered_networks as $network => $data) {
			if(!in_array($network, $enabled, true)){
				continue;
			}
			
			$buttons[] = [
				'network' => $network,
				'icon' => self::get_network_icon($network),
				'label' => $data['label'],  
				'share_url' => self::build_share_url($network, $post_id, $type),
			];
		}
		return $buttons;
	}

	static function get_network_icon($network) {
		$networks = self::get_networks();

		if(isset($networks[$network]) && isset($networks[$network]['icon'])){
			$icon = $networks[$network]['icon'];
			$icon = preg_replace('/\s(width|height)=["\']?\d*["\']?\s*/i', ' ', $icon);
			$icon = preg_replace('/\s+style="width:\s*\d+px;\s*height:\s*\d+px;\s*"/i', ' ', $icon);
			if (strpos($icon, 'fill="') === false && strpos($icon, 'stroke="') === false) {
				$icon = str_replace('<svg', '<svg style="fill:currentColor"', $icon);
			}
			return $icon;
		}
		return '<span class="dashicons dashicons-share"></span>';
	}



	static function append_sharebar($content) {

		if (get_the_ID() !== get_queried_object_id()) {
			return $content;
		}

		if (is_front_page() && !apply_filters('sharemypost_show_on_front_page', false)) {
			return $content;
		}

		if (class_exists('WooCommerce') && (is_checkout() || is_cart() || is_account_page())) {
			return $content;
		}

		$settings = \ShareMyPost\Util::get_settings();
		if(empty($settings['inline_enabled'])){
			return $content;
		}

		if(empty($settings['mobile']) && wp_is_mobile()){
			return $content;
		}

		$post_type = get_post_type();
		$allowed_types = (array) $settings['inline_post_types'];

		if(!in_array($post_type, $allowed_types, true)){
			return $content;
		}

		$html = self::get_share_bar_html(get_the_ID());
		if(empty($html)){
			return $content;
		}

		$html = wp_kses($html, self::get_allowed_html_tags());

		// Mark as run right before modifying the content so the next call is blocked
		$has_run = true;

		switch ($settings['inline_position']) {
			case 'above': 
				return $html . $content;
			case 'below': 
				return $content . $html;
			case 'both':  
				return $html . $content . $html;
			default:      
				return $content;
		}
	}

	static function resolve_bar_setting($settings, $primary_key, $fallback_key = null) {
		$defaults = \ShareMyPost\Util::get_default_settings();
		$default_value = array_key_exists($primary_key, $defaults) ? $defaults[$primary_key] : null;

		if(array_key_exists($primary_key, $settings) && $settings[$primary_key] !== $default_value){
			return $settings[$primary_key];
		}

		if($fallback_key !== null && array_key_exists($fallback_key, $settings)){
			return $settings[$fallback_key];
		}

		if($fallback_key !== null && array_key_exists($fallback_key, $defaults)){
			return $defaults[$fallback_key];
		}

		return $default_value;
	}

	static function get_share_bar_html($post_id, $is_floating = false) {

		$settings = \ShareMyPost\Util::get_settings();
		$type = 'inline';
		$buttons = self::build_share_buttons($post_id, $type);

		$buttons = apply_filters('sharemypost_share_bar_buttons', $buttons, $post_id, $is_floating, $settings);

		$color_type = $settings['inline_button_color_type'];
		$button_color = $settings['inline_button_color'];
		$show_labels_setting = $settings['inline_show_labels'];
		if(empty($buttons)){
			return '';
		}

		$wrapper_classes = [
			'sharemypost-share-bar',
			'sharemypost-inline-bar',
			'sharemypost-color-type-' . esc_attr($color_type),
			'sharemypost-shape-rounded',
			'sharemypost-style-filled',
			'sharemypost-size-normal',
		];

		$show_icon = in_array($show_labels_setting, ['icon_only', 'both'], true);
		$show_label = in_array($show_labels_setting, ['label_only', 'both'], true);
		$wrapper_classes[] = 'sharemypost-labels-' . esc_attr($show_labels_setting);

		$wrapper_classes = apply_filters('sharemypost_share_bar_wrapper_classes', $wrapper_classes, $post_id, $is_floating, $settings);

		$style_vars = [
			'--sharemypost-button-color:' . esc_attr($button_color),
			'--sharemypost-button-gap:10px',
			'--sharemypost-button-size:20px',
			'--sharemypost-button-font-size:' . max(10, round(20 * 0.33)) . 'px',
			'--sharemypost-button-padding:' . max(6, round(20 * 0.22)) . 'px ' . max(10, round(20 * 0.35)) . 'px',
			'--sharemypost-button-icon-size:' . max(20, round(20 * 0.70)) . 'px',
		];

		$style_vars = apply_filters('sharemypost_share_bar_style_vars', $style_vars, $post_id, $is_floating, $settings);

		$html = '<div class="' . esc_attr(implode(' ', $wrapper_classes)) . '" style="' . esc_attr(implode(';', $style_vars)) . '">';

			$html .= apply_filters('sharemypost_share_bar_before_buttons', '', $post_id, $is_floating, $settings, $type);

		$html .= '<div class="sharemypost-button-group">';

		// Share Buttons
		foreach($buttons as $btn){

			$btn_classes = ['sharemypost-share-button', 'sharemypost-network-' . esc_attr($btn['network'])];
			$btn_classes = apply_filters('sharemypost_share_bar_button_classes', $btn_classes, $btn, $post_id, $is_floating, $settings);

			$html .= '<a href="' . esc_url($btn['share_url']) . '"
				class="' . esc_attr(implode(' ', $btn_classes)) . '"
				data-post-id="' . esc_attr($post_id) . '"
				data-network="' . esc_attr($btn['network']) . '">';

			if(!empty($show_icon)){
				static $allowed_svg = null;
				if(null === $allowed_svg){
					$allowed_svg = [
						'svg' => [
							'viewbox' => true,
							'viewBox' => true,
							'xmlns' => true,
							'fill' => true,
							'stroke' => true,
							'stroke-width' => true,
							'stroke-linecap' => true,
							'stroke-linejoin' => true,
							'style' => true,
							'class' => true,
							'role' => true,
						],
						'path' => [
							'd' => true,
							'fill' => true,
							'stroke' => true,
							'stroke-width' => true,
							'stroke-linecap' => true,
							'stroke-linejoin' => true,
							'style' => true,
						],
						'circle' => [
							'cx' => true,
							'cy' => true,
							'r' => true,
							'fill' => true,
							'stroke' => true,
						],
						'rect' => [
							'x' => true,
							'y' => true,
							'width' => true,
							'height' => true,
							'rx' => true,
							'ry' => true,
							'fill' => true,
						],
						'g' => ['fill' => true, 'stroke' => true],
						'polygon' => ['points' => true, 'fill' => true],
						'line' => ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true],
						'polyline' => ['points' => true, 'fill' => true, 'stroke' => true],
						'ellipse' => ['cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true],
						'title' => [],
					];
				}
				$html .= '<span class="sharemypost-button-icon">'
					. wp_kses($btn['icon'], $allowed_svg) .
				'</span>';
			}

			if(!empty($show_label)){

				$html .= '<span class="sharemypost-button-label">'
					. esc_html($btn['label']) .
				'</span>';
			}

			$html = apply_filters('sharemypost_share_bar_button_inner', $html, $btn, $post_id, $is_floating, $settings);

			$html .= '</a>';
		}

		$html .= apply_filters('sharemypost_share_bar_extra_buttons', '', $post_id, $is_floating, $settings, $show_icon, $show_label);

		$html .= '</div>';

			$html .= apply_filters('sharemypost_share_bar_after_buttons', '', $post_id, $is_floating, $settings, $type);

		$html .= '</div>';

		return wp_kses(apply_filters('sharemypost_share_bar_html', $html, $post_id, $is_floating, $settings), self::get_allowed_html_tags());
	}

	static function get_allowed_html_tags() {
		return [
			'div' => [
				'class' => true,
				'style' => true,
				'id' => true,
			],
			'p' => [
				'class' => true,
			],
			'span' => [
				'class' => true,
				'style' => true,
			],
			'a' => [
				'href' => true,
				'class' => true,
				'target' => true,
				'rel' => true,
				'data-post-id' => true,
				'data-network' => true,
				'data-click-id' => true,
				'style' => true,
			],
			'svg' => [
				'viewbox' => true,
				'viewBox' => true,
				'xmlns' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'style' => true,
				'class' => true,
				'role' => true,
				'width' => true,
				'height' => true,
			],
			'path' => [
				'd' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'style' => true,
			],
			'circle' => [
				'cx' => true,
				'cy' => true,
				'r' => true,
				'fill' => true,
				'stroke' => true,
			],
			'rect' => [
				'x' => true,
				'y' => true,
				'width' => true,
				'height' => true,
				'rx' => true,
				'ry' => true,
				'fill' => true,
			],
			'g' => [
				'fill' => true,
				'stroke' => true,
			],
			'polygon' => [
				'points' => true,
				'fill' => true,
			],
			'line' => [
				'x1' => true,
				'y1' => true,
				'x2' => true,
				'y2' => true,
				'stroke' => true,
			],
			'polyline' => [
				'points' => true,
				'fill' => true,
				'stroke' => true,
			],
			'ellipse' => [
				'cx' => true,
				'cy' => true,
				'rx' => true,
				'ry' => true,
				'fill' => true,
				'stroke' => true,
			],
			'title' => [],
		];
	}
}