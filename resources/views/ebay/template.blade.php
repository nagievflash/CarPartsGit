<font rwr="1" style="">
    <title>Motor Elements</title>
    <style media="screen">
        * {
            margin:0;
            padding:0;
            outline:0;
            box-sizing: border-box;
        }
        header, main {
            font-family: "Inter", sans-serif;
            font-size: 16px;
        }
        .container {
            margin:0 auto;
            width:100%;
            max-width: 1200px;
            padding:0 15px;
        }
        header {
            padding-top:15px;
        }
        header .logo img {
            display: block;
            width: 150px;
            height: auto;
            text-align: center;
            padding-left: 0px;
        }
        .wrapper a, a:link, a:visited {
            color: #000;
            text-decoration: none;
        }
        .logo {
            transition:.2s ease-in-out opacity;
        }
        .logo:hover {
            opacity: 0.7;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        #nav {
            background: #5c5c5c;
            box-shadow: 0 0 15px rgba(0,0,0,.3);
        }
        .customer-support a {
            font-weight: 600;
            font-size: 18px;
            line-height: 28px;
        }
        .customer-support a span {
            color: #f51812;
        }
        .hold-toogle {
            display: none;
        }
        .menu-holder {
            width:100%;
        }
        .open-check {
            display: none;
        }
        .close-menu {
            display: none;
        }
        .menu-holder ul {
            display: flex;
            width: 100%;
            list-style: none;
            justify-content: flex-start;
            align-items: center;
            margin: 0 -15px;
            font-size: 18px;
            font-weight: 600;
            margin-top: 10px;
        }
        .menu-holder ul li {
        }
        .menu-holder ul li a {
            color: #fff;
            transition:.3s ease-in-out all;
            position: relative;
            overflow:hidden;
            display:block;
            line-height: 28px;
            padding:20px 20px;
            text-align: center;
        }
        .menu-holder ul li a:before {
            content:"";
            width:100%;
            height:7px;
            bottom:0px;
            left:0;
            background-color:#ff5c5c;
            position: absolute;
            transform:translateX(-200px);
            transition:.3s ease-in-out all;
        }
        .menu-holder ul li a:hover {
            color:#fff;
        }
        .menu-holder ul li a:hover:before {
            transform:translateX(0px);
        }
        .gallery-section {
            margin-top:0px;
            background: #f9f9f9;
            padding:70px 0;
        }
        .gallery-section h3 {
            font-size:28px;
        }
        .gallery {
            height: 800px;
            overflow: hidden;
            background: #ffffff;
            border-radius: 30px;
            margin: 60px 0;
            position: relative;
            padding-right:120px;
        }
        .gallery .gallery-controls {
            display: flex;
            justify-content: flex-start;
            /* padding-top: 120px; */
            position: absolute;
            top: 30px;
            right: 30px;
            width: 120px;
            flex-wrap: wrap;
            height: 120px;
            z-index: 1;
        }
        .gallery .small_label {
            width: 100px;
            height: 100px;
            margin-top: 15px;
            display: flex;
            cursor: pointer;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            box-shadow: 0 0 15px rgb(0 0 0 / 25%);
            border-radius: 10px;
            opacity: .6;
        }
        .gallery input[name="slide_switch"] {
            opacity: 0;
            visibility: hidden;
            display: none!important;
        }
        .gallery-controls-images {
            position: relative;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gallery-controls-images img {
            opacity: 0;
            transition: .4s ease-in-out all;
            width:85%;
            position: absolute;
            color:#fff;
        }
        #preview_id1:checked ~ .gallery-images .gallery-controls-images img#preview_img_1 {
            opacity: 1;
        }
        #preview_id2:checked ~ .gallery-images .gallery-controls-images img#preview_img_2 {
            opacity: 1;
        }
        #preview_id3:checked ~ .gallery-images .gallery-controls-images img#preview_img_3 {
            opacity: 1;
        }
        #preview_id4:checked ~ .gallery-images .gallery-controls-images img#preview_img_4 {
            opacity: 1;
        }
        #preview_id5:checked ~ .gallery-images .gallery-controls-images img#preview_img_5 {
            opacity: 1;
        }
        #preview_id6:checked ~ .gallery-images .gallery-controls-images img#preview_img_6 {
            opacity: 1;
        }
        #preview_id7:checked ~ .gallery-images .gallery-controls-images img#preview_img_7 {
            opacity: 1;
        }

        #preview_id1:checked ~ .gallery-controls #label1 {
            opacity: 1;
        }
        #preview_id2:checked ~ .gallery-controls #label2 {
            opacity: 1;
        }
        #preview_id3:checked ~ .gallery-controls #label3 {
            opacity: 1;
        }
        #preview_id4:checked ~ .gallery-controls #label4 {
            opacity: 1;
        }
        #preview_id5:checked ~ .gallery-controls #label5 {
            opacity: 1;
        }
        #preview_id6:checked ~ .gallery-controls #label6 {
            opacity: 1;
        }
        #preview_id7:checked ~ .gallery-controls #label7 {
            opacity: 1;
        }

        .information-box {
            padding: 120px 0;
        }
        .information-tabs input[name="tabs"] {
            display: none;
        }
        .information-tabs .tabs-header label {
            line-height: 45px;
            font-size: 28px;
            padding: 15px 5px;
            cursor:pointer;
            overflow: hidden;
            margin:0 15px;
            transition: .1s ease border;
        }
        .information-tabs input#specifications-tab:checked ~ label#specifications-tab-label {
            border-top:4px solid #ff5c5c;
        }
        .information-tabs input#compatibilities-tab:checked ~ label#compatibilities-tab-label {
            border-top:4px solid #ff5c5c;
        }
        .information-tabs input#shipping-tab:checked ~ label#shipping-tab-label {
            border-top:4px solid #ff5c5c;
        }
        .information-tabs input#warranty-tab:checked ~ label#warranty-tab-label {
            border-top:4px solid #ff5c5c;
        }

        .tabs-header .tab-content {
            display: none;
            transition: .2s ease-in-out all;
            padding: 50px 50px 70px;
            margin-top: 50px;
            background: #f5f5f5;
        }
        .information-tabs input#specifications-tab:checked ~ #specifications {
            display: block;
        }
        .information-tabs input#compatibilities-tab:checked ~ #compatibilities {
            display: block;
        }
        .information-tabs input#shipping-tab:checked ~ #shipping {
            display: block;
        }
        .information-tabs input#warranty-tab:checked ~ #warranty {
            display: block;
        }

        .tabs-header .tab-content h4 {
            margin: 25px 0;
            font-size: 26px;
        }
        .tabs-header .tab-content .PropertyList {
            font-size:22px;
            line-height: 34px;
        }
        .tabs-header .tab-content .PropertyList span {
            color:#000;
            font-weight: 600;
        }
        .footer {
            padding: 60px 0 50px;
            font-size: 24px;
            text-align: center;
            background: #f5f5f5;
        }
        #specifications .PropertyList {
            margin:20px 0;
        }
    </style>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <header class="wrapper" id="header">
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <a href="https://www.ebay.com/str/motorelements" target="_blank">
                        <img alt="image" src="https://i.ebayimg.com/images/g/OykAAOSwIwBherRk/s-l500.png">
                    </a>
                </div>
                <div class="customer-support">
                    <a href="https://www.ebay.com/cnt/IntermediatedFAQ?ReturnUserEmail=&requested=motor_elements&_caprdt=1">
                        Customer <span>Support</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="header-menu">
            <nav id="nav">

                <div class="container">
                    <input class="open-check" id="open-menu" type="checkbox">
                    <div class="hold-toogle">
                        <label class="toogle-menu" for="open-menu">Menu <span class="t"></span> <span class="c"></span> <span class="b"></span> </label>
                    </div>
                    <div class="menu-holder">
                        <label class="close-menu" for="open-menu">Close Menu <i class="fa fa-close" aria-hidden="true"></i> </label>
                        <ul id="top-nav" data-editor="nav">
                            <li><a href="https://www.ebay.com/str/motorelements" target="_blank">Shop</a></li>
                            <li><a href="https://www.ebay.com/str/motorelements" target="_blank">Visit Our Store</a></li>
                            <li><a href="https://www.ebay.com/usr/motor_elements" target="_blank">About Us</a></li>
                            <li><a href="https://www.ebay.com/fdbk/feedback_profile/motor_elements?filter=feedback_page:All" target="_blank">Reviews</a></li>
                            <li><a href="https://www.ebay.com/cnt/IntermediatedFAQ?ReturnUserEmail=&amp;requested=motor_elements&amp;_caprdt=1" target="_blank">Contact Us</a></li>
                        </ul>
                    </div>
                </div>

            </nav>
        </div>
    </header>
    <main class="wrapper" id="main">
        <div class="gallery-section">
            <div class="container">
                <h3>{{$title}}</h3>
                <div class="gallery">

                    @foreach ($images as $key => $image)
                        @if ($key == 0)
                            <input name="slide_switch" id="preview_id{{$key + 1}}" checked="checked" type="radio">
                        @else
                            <input name="slide_switch" id="preview_id{{$key + 1}}" type="radio">
                        @endif
                    @endforeach
                    <div class="gallery-controls">
                        @foreach ($images as $key => $image)
                            <label class="small_label" id="label{{$key + 1}}" for="preview_id{{$key + 1}}" style="background-image:url({{$image}})">
                            </label>
                        @endforeach
                    </div>
                    <div class="gallery-images">
                        <div class="gallery-controls-images">
                            @foreach ($images as $key => $image)
                                <img class="big_preview_img" id="preview_img_{{$key}}" src="{{$image}}">
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="information-box">
            <div class="container">
                <div class="information-tabs">
                    <div class="tabs-header">
                        <input checked="checked" id="specifications-tab" name="tabs" type="radio">
                        <label id="specifications-tab-label" for="specifications-tab">Specifications</label>
                        <input id="compatibilities-tab" name="tabs" type="radio">
                        <label id="compatibilities-tab-label" for="compatibilities-tab">Compatibilities</label>
                        <input id="shipping-tab" name="tabs" type="radio">
                        <label id="shipping-tab-label" for="shipping-tab" >Shipping Policies</label>
                        <input id="warranty-tab" name="tabs" type="radio">
                        <label id="warranty-tab-label" for="warranty-tab" >Warranty / Return</label>

                        <div id="specifications" class="tab-content">
                            <h4>Item Specifications</h4>
                            @foreach ($attributes as $attribute)
                                @if ($attribute->name != 'Prop 65 Warning')
                                    <div class="PropertyList">
                                        <p><span>{{$attribute->name}}:</span> {{$attribute->value}}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>


                        <div id="compatibilities" class="tab-content">
                            <h4>Compatibilities</h4>
                            @foreach ($fitments as $fitment)
                            <div class="PropertyList">
                                <p>{{$fitment}}</p>
                            </div>
                            @endforeach
                        </div>

                        <div id="shipping" class="tab-content">
                            <h4>Shipping Policies</h4>
                            <div class="PropertyList">
                                Items will be shipped within 1-2 business days of order placement. Shipping Exclusions: Alaska/Hawaii, US Protectorates, APO/FPO. No International Shipping. Some orders containing multiple parts may be delivered in more than one package, in this case you will receive multiple tracking numbers.
                            </div>
                        </div>

                        <div id="warranty" class="tab-content">
                            <h4>Satisfaction Guarantee</h4>
                            <div class="PropertyList">
                                We aim to provide our customers with the best service, and highest quality products possible. If you are unsure about the fitment of any of our products, please reach out us with your vehicles MAKE, MODEL, and YEAR and we will be happy to confirm fitment.
                            </div>
                            <h4 style="margin-top:50px;">Warranty</h4>
                            <div class="PropertyList">
                                This item comes with a 1 year warranty that is valid for a one time replacement of the purchased product. Should the product be unavailable at the time a replacement is requested, we will refund your payment less return shipping and handling charges. See further details below.
                            </div>
                            <h4 style="margin-top:50px;">Returns</h4>
                            <div class="PropertyList">
                                All orders are eligible for return within 30 days of purchase.  Buyer is responsible for costs of return shipping.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">
            <div class="container">
                <p>Copyright @ Motor Elements, All Rights Reserved</p>
            </div>
        </div>
    </main>
</font>
