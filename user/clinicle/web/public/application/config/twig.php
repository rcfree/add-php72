  


<!DOCTYPE html>
<html>
  <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# githubog: http://ogp.me/ns/fb/githubog#">
    <meta charset='utf-8'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>codeigniter-twig/application/config/twig.php at master · dilantha/codeigniter-twig · GitHub</title>
    <link rel="search" type="application/opensearchdescription+xml" href="/opensearch.xml" title="GitHub" />
    <link rel="fluid-icon" href="https://github.com/fluidicon.png" title="GitHub" />
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-114.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-144.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144.png" />
    <link rel="logo" type="image/svg" href="http://github-media-downloads.s3.amazonaws.com/github-logo.svg" />
    <link rel="assets" href="https://a248.e.akamai.net/assets.github.com/">
    <link rel="xhr-socket" href="/_sockets" />
    


    <meta name="msapplication-TileImage" content="/windows-tile.png" />
    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="selected-link" value="repo_source" data-pjax-transient />
    <meta content="collector.githubapp.com" name="octolytics-host" /><meta content="github" name="octolytics-app-id" />

    
    
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />

    <meta content="authenticity_token" name="csrf-param" />
<meta content="mTZLVH2rWWQT26O2py/FlPiF/RRmW87Mw/2NeN48Mh0=" name="csrf-token" />

    <link href="https://a248.e.akamai.net/assets.github.com/assets/github-28cb64109a7ebb60276b297a5459cd080aa82add.css" media="all" rel="stylesheet" type="text/css" />
    <link href="https://a248.e.akamai.net/assets.github.com/assets/github2-dbfecd24131ff912b4e9fe4d5a365661e862a5c5.css" media="all" rel="stylesheet" type="text/css" />
    


      <script src="https://a248.e.akamai.net/assets.github.com/assets/frameworks-4c434fa1705bf526e191eec0cd8fab1a5ce3e326.js" type="text/javascript"></script>
      <script src="https://a248.e.akamai.net/assets.github.com/assets/github-4dd8fab0e6d453f2e67265af313b0a4c78760d52.js" type="text/javascript"></script>
      
      <meta http-equiv="x-pjax-version" content="ec8147761b2439f941f2ea8a2147718c">

        <link data-pjax-transient rel='permalink' href='/dilantha/codeigniter-twig/blob/2c52923d8bc26f8481522eae6c30f5a3c5545201/application/config/twig.php'>
    <meta property="og:title" content="codeigniter-twig"/>
    <meta property="og:type" content="githubog:gitrepository"/>
    <meta property="og:url" content="https://github.com/dilantha/codeigniter-twig"/>
    <meta property="og:image" content="https://secure.gravatar.com/avatar/b3afcde8213118410c15c51bcc742e9f?s=420&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png"/>
    <meta property="og:site_name" content="GitHub"/>
    <meta property="og:description" content="codeigniter-twig - A basic CodeIgniter library for using the Twig template language"/>
    <meta property="twitter:card" content="summary"/>
    <meta property="twitter:site" content="@GitHub">
    <meta property="twitter:title" content="dilantha/codeigniter-twig"/>

    <meta name="description" content="codeigniter-twig - A basic CodeIgniter library for using the Twig template language" />


    <meta content="450922" name="octolytics-dimension-user_id" /><meta content="dilantha" name="octolytics-dimension-user_login" /><meta content="1598100" name="octolytics-dimension-repository_id" /><meta content="dilantha/codeigniter-twig" name="octolytics-dimension-repository_nwo" /><meta content="true" name="octolytics-dimension-repository_public" /><meta content="true" name="octolytics-dimension-repository_is_fork" /><meta content="1016729" name="octolytics-dimension-repository_parent_id" /><meta content="altrano/codeigniter-twig" name="octolytics-dimension-repository_parent_nwo" /><meta content="357090" name="octolytics-dimension-repository_network_root_id" /><meta content="jamiepittock/codeigniter-twig" name="octolytics-dimension-repository_network_root_nwo" />
  <link href="https://github.com/dilantha/codeigniter-twig/commits/master.atom" rel="alternate" title="Recent Commits to codeigniter-twig:master" type="application/atom+xml" />

  </head>


  <body class="logged_out page-blob macintosh vis-public fork env-production  ">
    <div id="wrapper">

      
      
      

      
      <div class="header header-logged-out">
  <div class="container clearfix">

    <a class="header-logo-wordmark" href="https://github.com/">Github</a>

    <div class="header-actions">
      <a class="button primary" href="/signup">Sign up</a>
      <a class="button" href="/login?return_to=%2Fdilantha%2Fcodeigniter-twig%2Fblob%2Fmaster%2Fapplication%2Fconfig%2Ftwig.php">Sign in</a>
    </div>

    <div class="command-bar js-command-bar  in-repository">


      <ul class="top-nav">
          <li class="explore"><a href="/explore">Explore</a></li>
        <li class="features"><a href="/features">Features</a></li>
          <li class="enterprise"><a href="http://enterprise.github.com/">Enterprise</a></li>
          <li class="blog"><a href="/blog">Blog</a></li>
      </ul>
        <form accept-charset="UTF-8" action="/search" class="command-bar-form" id="top_search_form" method="get">
  <a href="/search/advanced" class="advanced-search-icon tooltipped downwards command-bar-search" id="advanced_search" title="Advanced search"><span class="octicon octicon-gear "></span></a>

  <input type="text" data-hotkey="/ s" name="q" id="js-command-bar-field" placeholder="Search or type a command" tabindex="1" autocapitalize="off"
    
      data-repo="dilantha/codeigniter-twig"
      data-branch="master"
      data-sha="b06fe3c0e4c6d59000ea6b964ce845906ff3b278"
  >

    <input type="hidden" name="nwo" value="dilantha/codeigniter-twig" />

    <div class="select-menu js-menu-container js-select-menu search-context-select-menu">
      <span class="minibutton select-menu-button js-menu-target">
        <span class="js-select-button">This repository</span>
      </span>

      <div class="select-menu-modal-holder js-menu-content js-navigation-container">
        <div class="select-menu-modal">

          <div class="select-menu-item js-navigation-item selected">
            <span class="select-menu-item-icon octicon octicon-check"></span>
            <input type="radio" class="js-search-this-repository" name="search_target" value="repository" checked="checked" />
            <div class="select-menu-item-text js-select-button-text">This repository</div>
          </div> <!-- /.select-menu-item -->

          <div class="select-menu-item js-navigation-item">
            <span class="select-menu-item-icon octicon octicon-check"></span>
            <input type="radio" name="search_target" value="global" />
            <div class="select-menu-item-text js-select-button-text">All repositories</div>
          </div> <!-- /.select-menu-item -->

        </div>
      </div>
    </div>

  <span class="octicon help tooltipped downwards" title="Show command bar help">
    <span class="octicon octicon-question"></span>
  </span>


  <input type="hidden" name="ref" value="cmdform">

  <div class="divider-vertical"></div>

</form>
    </div>

  </div>
</div>


      


            <div class="site hfeed" itemscope itemtype="http://schema.org/WebPage">
      <div class="hentry">
        
        <div class="pagehead repohead instapaper_ignore readability-menu ">
          <div class="container">
            <div class="title-actions-bar">
              

<ul class="pagehead-actions">



    <li>
      <a href="/login?return_to=%2Fdilantha%2Fcodeigniter-twig"
        class="minibutton js-toggler-target star-button entice tooltipped upwards"
        title="You must be signed in to use this feature" rel="nofollow">
        <span class="octicon octicon-star"></span>Star
      </a>
      <a class="social-count js-social-count" href="/dilantha/codeigniter-twig/stargazers">
        45
      </a>
    </li>
    <li>
      <a href="/login?return_to=%2Fdilantha%2Fcodeigniter-twig"
        class="minibutton js-toggler-target fork-button entice tooltipped upwards"
        title="You must be signed in to fork a repository" rel="nofollow">
        <span class="octicon octicon-git-branch"></span>Fork
      </a>
      <a href="/dilantha/codeigniter-twig/network" class="social-count">
        28
      </a>
    </li>
</ul>

              <h1 itemscope itemtype="http://data-vocabulary.org/Breadcrumb" class="entry-title public">
                <span class="repo-label"><span>public</span></span>
                <span class="mega-octicon octicon-repo"></span>
                <span class="author vcard">
                  <a href="/dilantha" class="url fn" itemprop="url" rel="author">
                  <span itemprop="title">dilantha</span>
                  </a></span> /
                <strong><a href="/dilantha/codeigniter-twig" class="js-current-repository">codeigniter-twig</a></strong>
                  <span class="fork-flag">
                    <span class="text">forked from <a href="/altrano/codeigniter-twig">altrano/codeigniter-twig</a></span>
                  </span>
              </h1>
            </div>

            
  <ul class="tabs">
    <li class="pulse-nav"><a href="/dilantha/codeigniter-twig/pulse" class="js-selected-navigation-item " data-selected-links="pulse /dilantha/codeigniter-twig/pulse" rel="nofollow"><span class="octicon octicon-pulse"></span></a></li>
    <li><a href="/dilantha/codeigniter-twig" class="js-selected-navigation-item selected" data-selected-links="repo_source repo_downloads repo_commits repo_tags repo_branches /dilantha/codeigniter-twig">Code</a></li>
    <li><a href="/dilantha/codeigniter-twig/network" class="js-selected-navigation-item " data-selected-links="repo_network /dilantha/codeigniter-twig/network">Network</a></li>
    <li><a href="/dilantha/codeigniter-twig/pulls" class="js-selected-navigation-item " data-selected-links="repo_pulls /dilantha/codeigniter-twig/pulls">Pull Requests <span class='counter'>0</span></a></li>

      <li><a href="/dilantha/codeigniter-twig/issues" class="js-selected-navigation-item " data-selected-links="repo_issues /dilantha/codeigniter-twig/issues">Issues <span class='counter'>1</span></a></li>



    <li><a href="/dilantha/codeigniter-twig/graphs" class="js-selected-navigation-item " data-selected-links="repo_graphs repo_contributors /dilantha/codeigniter-twig/graphs">Graphs</a></li>


  </ul>
  
<div class="tabnav">

  <span class="tabnav-right">
    <ul class="tabnav-tabs">
          <li><a href="/dilantha/codeigniter-twig/tags" class="js-selected-navigation-item tabnav-tab" data-selected-links="repo_tags /dilantha/codeigniter-twig/tags">Tags <span class="counter blank">0</span></a></li>
    </ul>
  </span>

  <div class="tabnav-widget scope">


    <div class="select-menu js-menu-container js-select-menu js-branch-menu">
      <a class="minibutton select-menu-button js-menu-target" data-hotkey="w" data-ref="master">
        <span class="octicon octicon-git-branch"></span>
        <i>branch:</i>
        <span class="js-select-button">master</span>
      </a>

      <div class="select-menu-modal-holder js-menu-content js-navigation-container">

        <div class="select-menu-modal">
          <div class="select-menu-header">
            <span class="select-menu-title">Switch branches/tags</span>
            <span class="octicon octicon-remove-close js-menu-close"></span>
          </div> <!-- /.select-menu-header -->

          <div class="select-menu-filters">
            <div class="select-menu-text-filter">
              <input type="text" id="commitish-filter-field" class="js-filterable-field js-navigation-enable" placeholder="Filter branches/tags">
            </div>
            <div class="select-menu-tabs">
              <ul>
                <li class="select-menu-tab">
                  <a href="#" data-tab-filter="branches" class="js-select-menu-tab">Branches</a>
                </li>
                <li class="select-menu-tab">
                  <a href="#" data-tab-filter="tags" class="js-select-menu-tab">Tags</a>
                </li>
              </ul>
            </div><!-- /.select-menu-tabs -->
          </div><!-- /.select-menu-filters -->

          <div class="select-menu-list select-menu-tab-bucket js-select-menu-tab-bucket css-truncate" data-tab-filter="branches">

            <div data-filterable-for="commitish-filter-field" data-filterable-type="substring">

                <div class="select-menu-item js-navigation-item selected">
                  <span class="select-menu-item-icon octicon octicon-check"></span>
                  <a href="/dilantha/codeigniter-twig/blob/master/application/config/twig.php" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="master" rel="nofollow" title="master">master</a>
                </div> <!-- /.select-menu-item -->
            </div>

              <div class="select-menu-no-results">Nothing to show</div>
          </div> <!-- /.select-menu-list -->


          <div class="select-menu-list select-menu-tab-bucket js-select-menu-tab-bucket css-truncate" data-tab-filter="tags">
            <div data-filterable-for="commitish-filter-field" data-filterable-type="substring">

            </div>

            <div class="select-menu-no-results">Nothing to show</div>

          </div> <!-- /.select-menu-list -->

        </div> <!-- /.select-menu-modal -->
      </div> <!-- /.select-menu-modal-holder -->
    </div> <!-- /.select-menu -->

  </div> <!-- /.scope -->

  <ul class="tabnav-tabs">
    <li><a href="/dilantha/codeigniter-twig" class="selected js-selected-navigation-item tabnav-tab" data-selected-links="repo_source /dilantha/codeigniter-twig">Files</a></li>
    <li><a href="/dilantha/codeigniter-twig/commits/master" class="js-selected-navigation-item tabnav-tab" data-selected-links="repo_commits /dilantha/codeigniter-twig/commits/master">Commits</a></li>
    <li><a href="/dilantha/codeigniter-twig/branches" class="js-selected-navigation-item tabnav-tab" data-selected-links="repo_branches /dilantha/codeigniter-twig/branches" rel="nofollow">Branches <span class="counter ">1</span></a></li>
  </ul>

</div>

  
  
  


            
          </div>
        </div><!-- /.repohead -->

        <div id="js-repo-pjax-container" class="container context-loader-container" data-pjax-container>
          


<!-- blob contrib key: blob_contributors:v21:09dbb26414b549338de8380d5cbf1580 -->
<!-- blob contrib frag key: views10/v8/blob_contributors:v21:09dbb26414b549338de8380d5cbf1580 -->


<div id="slider">
    <div class="frame-meta">

      <p title="This is a placeholder element" class="js-history-link-replace hidden"></p>

        <div class="breadcrumb">
          <span class='bold'><span itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/dilantha/codeigniter-twig" class="js-slide-to" data-branch="master" data-direction="back" itemscope="url"><span itemprop="title">codeigniter-twig</span></a></span></span><span class="separator"> / </span><span itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/dilantha/codeigniter-twig/tree/master/application" class="js-slide-to" data-branch="master" data-direction="back" itemscope="url"><span itemprop="title">application</span></a></span><span class="separator"> / </span><span itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/dilantha/codeigniter-twig/tree/master/application/config" class="js-slide-to" data-branch="master" data-direction="back" itemscope="url"><span itemprop="title">config</span></a></span><span class="separator"> / </span><strong class="final-path">twig.php</strong> <span class="js-zeroclipboard zeroclipboard-button" data-clipboard-text="application/config/twig.php" data-copied-hint="copied!" title="copy to clipboard"><span class="octicon octicon-clippy"></span></span>
        </div>

      <a href="/dilantha/codeigniter-twig/find/master" class="js-slide-to" data-hotkey="t" style="display:none">Show File Finder</a>


        
  <div class="commit file-history-tease">
    <img class="main-avatar" height="24" src="https://secure.gravatar.com/avatar/b3afcde8213118410c15c51bcc742e9f?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png" width="24" />
    <span class="author"><a href="/dilantha" rel="author">dilantha</a></span>
    <time class="js-relative-date" datetime="2011-04-11T02:26:07-07:00" title="2011-04-11 02:26:07">April 11, 2011</time>
    <div class="commit-title">
        <a href="/dilantha/codeigniter-twig/commit/f4969b8c411a773c978a4035774fe606ebe94ef9" class="message">Moving things around for CodeIgniter 2.x. Added CI helper function ma…</a>
    </div>

    <div class="participation">
      <p class="quickstat"><a href="#blob_contributors_box" rel="facebox"><strong>1</strong> contributor</a></p>
      
    </div>
    <div id="blob_contributors_box" style="display:none">
      <h2 class="facebox-header">Users who have contributed to this file</h2>
      <ul class="facebox-user-list">
        <li class="facebox-user-list-item">
          <img height="24" src="https://secure.gravatar.com/avatar/b3afcde8213118410c15c51bcc742e9f?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png" width="24" />
          <a href="/dilantha">dilantha</a>
        </li>
      </ul>
    </div>
  </div>


    </div><!-- ./.frame-meta -->

    <div class="frames">
      <div class="frame" data-permalink-url="/dilantha/codeigniter-twig/blob/2c52923d8bc26f8481522eae6c30f5a3c5545201/application/config/twig.php" data-title="codeigniter-twig/application/config/twig.php at master · dilantha/codeigniter-twig · GitHub" data-type="blob">

        <div id="files" class="bubble">
          <div class="file">
            <div class="meta">
              <div class="info">
                <span class="icon"><b class="octicon octicon-file-text"></b></span>
                <span class="mode" title="File Mode">file</span>
                  <span>5 lines (3 sloc)</span>
                <span>0.163 kb</span>
              </div>
              <div class="actions">
                <div class="button-group">
                      <a class="minibutton js-entice" href=""
                         data-entice="You must be signed in and on a branch to make or propose changes">Edit</a>
                  <a href="/dilantha/codeigniter-twig/raw/master/application/config/twig.php" class="button minibutton " id="raw-url">Raw</a>
                    <a href="/dilantha/codeigniter-twig/blame/master/application/config/twig.php" class="button minibutton ">Blame</a>
                  <a href="/dilantha/codeigniter-twig/commits/master/application/config/twig.php" class="button minibutton " rel="nofollow">History</a>
                </div><!-- /.button-group -->
              </div><!-- /.actions -->

            </div>
                <div class="blob-wrapper data type-php js-blob-data">
      <table class="file-code file-diff">
        <tr class="file-code-line">
          <td class="blob-line-nums">
            <span id="L1" rel="#L1">1</span>
<span id="L2" rel="#L2">2</span>
<span id="L3" rel="#L3">3</span>
<span id="L4" rel="#L4">4</span>
<span id="L5" rel="#L5">5</span>

          </td>
          <td class="blob-line-code">
                  <div class="highlight"><pre><div class='line' id='LC1'><span class="o">&lt;?</span><span class="nx">php</span> <span class="k">if</span> <span class="p">(</span><span class="o">!</span><span class="nb">defined</span><span class="p">(</span><span class="s1">&#39;BASEPATH&#39;</span><span class="p">))</span> <span class="k">exit</span><span class="p">(</span><span class="s1">&#39;No direct script access allowed&#39;</span><span class="p">);</span></div><div class='line' id='LC2'>&nbsp;</div><div class='line' id='LC3'><span class="nv">$config</span><span class="p">[</span><span class="s1">&#39;template_dir&#39;</span><span class="p">]</span> <span class="o">=</span> <span class="nx">APPPATH</span><span class="o">.</span><span class="s1">&#39;views&#39;</span><span class="p">;</span></div><div class='line' id='LC4'><br/></div><div class='line' id='LC5'><span class="nv">$config</span><span class="p">[</span><span class="s1">&#39;cache_dir&#39;</span><span class="p">]</span> <span class="o">=</span> <span class="nx">APPPATH</span><span class="o">.</span><span class="s1">&#39;cache/twig&#39;</span><span class="p">;</span></div></pre></div>
          </td>
        </tr>
      </table>
  </div>

          </div>
        </div>

        <a href="#jump-to-line" rel="facebox" data-hotkey="l" class="js-jump-to-line" style="display:none">Jump to Line</a>
        <div id="jump-to-line" style="display:none">
          <h2>Jump to Line</h2>
          <form accept-charset="UTF-8" class="js-jump-to-line-form">
            <input class="textfield js-jump-to-line-field" type="text">
            <div class="full-button">
              <button type="submit" class="button">Go</button>
            </div>
          </form>
        </div>

      </div>
    </div>
</div>

<div id="js-frame-loading-template" class="frame frame-loading large-loading-area" style="display:none;">
  <img class="js-frame-loading-spinner" src="https://a248.e.akamai.net/assets.github.com/images/spinners/octocat-spinner-128.gif" height="64" width="64">
</div>


        </div>
      </div>
      <div class="modal-backdrop"></div>
    </div>

      <div id="footer-push"></div><!-- hack for sticky footer -->
    </div><!-- end of wrapper - hack for sticky footer -->

      <!-- footer -->
      <div id="footer">
  <div class="container clearfix">

      <dl class="footer_nav">
        <dt>GitHub</dt>
        <dd><a href="/about">About us</a></dd>
        <dd><a href="/blog">Blog</a></dd>
        <dd><a href="/contact">Contact &amp; support</a></dd>
        <dd><a href="http://enterprise.github.com/">GitHub Enterprise</a></dd>
        <dd><a href="http://status.github.com/">Site status</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>Applications</dt>
        <dd><a href="http://mac.github.com/">GitHub for Mac</a></dd>
        <dd><a href="http://windows.github.com/">GitHub for Windows</a></dd>
        <dd><a href="http://eclipse.github.com/">GitHub for Eclipse</a></dd>
        <dd><a href="http://mobile.github.com/">GitHub mobile apps</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>Services</dt>
        <dd><a href="http://get.gaug.es/">Gauges: Web analytics</a></dd>
        <dd><a href="http://speakerdeck.com">Speaker Deck: Presentations</a></dd>
        <dd><a href="https://gist.github.com">Gist: Code snippets</a></dd>
        <dd><a href="http://jobs.github.com/">Job board</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>Documentation</dt>
        <dd><a href="http://help.github.com/">GitHub Help</a></dd>
        <dd><a href="http://developer.github.com/">Developer API</a></dd>
        <dd><a href="http://github.github.com/github-flavored-markdown/">GitHub Flavored Markdown</a></dd>
        <dd><a href="http://pages.github.com/">GitHub Pages</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>More</dt>
        <dd><a href="http://training.github.com/">Training</a></dd>
        <dd><a href="/edu">Students &amp; teachers</a></dd>
        <dd><a href="http://shop.github.com">The Shop</a></dd>
        <dd><a href="/plans">Plans &amp; pricing</a></dd>
        <dd><a href="http://octodex.github.com/">The Octodex</a></dd>
      </dl>

      <hr class="footer-divider">


    <p class="right">&copy; 2013 <span title="0.05042s from fe1.rs.github.com">GitHub</span>, Inc. All rights reserved.</p>
    <a class="left" href="/">
      <span class="mega-octicon octicon-mark-github"></span>
    </a>
    <ul id="legal">
        <li><a href="/site/terms">Terms of Service</a></li>
        <li><a href="/site/privacy">Privacy</a></li>
        <li><a href="/security">Security</a></li>
    </ul>

  </div><!-- /.container -->

</div><!-- /.#footer -->


    <div class="fullscreen-overlay js-fullscreen-overlay" id="fullscreen_overlay">
  <div class="fullscreen-container js-fullscreen-container">
    <div class="textarea-wrap">
      <textarea name="fullscreen-contents" id="fullscreen-contents" class="js-fullscreen-contents" placeholder="" data-suggester="fullscreen_suggester"></textarea>
          <div class="suggester-container">
              <div class="suggester fullscreen-suggester js-navigation-container" id="fullscreen_suggester"
                 data-url="/dilantha/codeigniter-twig/suggestions/commit">
              </div>
          </div>
    </div>
  </div>
  <div class="fullscreen-sidebar">
    <a href="#" class="exit-fullscreen js-exit-fullscreen tooltipped leftwards" title="Exit Zen Mode">
      <span class="mega-octicon octicon-screen-normal"></span>
    </a>
    <a href="#" class="theme-switcher js-theme-switcher tooltipped leftwards"
      title="Switch themes">
      <span class="octicon octicon-color-mode"></span>
    </a>
  </div>
</div>



    <div id="ajax-error-message" class="flash flash-error">
      <span class="octicon octicon-alert"></span>
      Something went wrong with that request. Please try again.
      <a href="#" class="octicon octicon-remove-close ajax-error-dismiss"></a>
    </div>

    
    <span id='server_response_time' data-time='0.05080' data-host='fe1'></span>
    
  </body>
</html>

