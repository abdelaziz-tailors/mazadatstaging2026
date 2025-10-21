<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $page->name ?? 'سياسة الخصوصية' }} - VLOG</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #ffffff;
            color: #333;
            line-height: 1.8;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
        }
        
        .page-header {
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }
        
        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #000;
            margin-bottom: 15px;
        }
        
        .meta-info {
            color: #666;
            font-size: 0.95rem;
        }
        
        @if($page && $page->image)
        .page-image {
            margin: 30px 0;
            text-align: center;
        }
        
        .page-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        @endif
        
        .content {
            font-size: 1.05rem;
            line-height: 1.9;
            color: #333;
        }
        
        .content p {
            margin-bottom: 20px;
        }
        
        .content h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-top: 40px;
            margin-bottom: 20px;
            color: #000;
        }
        
        .content h3 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-top: 30px;
            margin-bottom: 15px;
            color: #222;
        }
        
        .content ul, .content ol {
            margin: 20px 0;
            padding-right: 25px;
        }
        
        .content li {
            margin-bottom: 10px;
        }
        
        .content strong {
            font-weight: 600;
            color: #000;
        }
        
        hr {
            border: none;
            border-top: 1px solid #e0e0e0;
            margin: 40px 0;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 30px;
            color: #666;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: #000;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .content {
                font-size: 1rem;
            }
            
            .content h2 {
                font-size: 1.5rem;
            }
            
            .content h3 {
                font-size: 1.2rem;
            }
        }
        
        /* Print Styles */
        @media print {
            .back-link {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-link">← العودة للرئيسية</a>
        
        <div class="page-header">
            <h1>{{ $page->name ?? 'سياسة الخصوصية' }}</h1>
            <p class="meta-info">آخر تحديث: {{ now()->translatedFormat('F d, Y') }}</p>
        </div>
        
     
        
        <div class="content">
            @if($page && $page->description)
                {!! nl2br(e($page->description)) !!}
            @else
                <p>محتوى سياسة الخصوصية غير متوفر حالياً.</p>
                <p>نحن نهتم بخصوصيتك وحماية بياناتك الشخصية. باستخدام خدماتنا، فإنك توافق على شروط سياسة الخصوصية هذه.</p>
            @endif
        </div>
    </div>
</body>
</html>

