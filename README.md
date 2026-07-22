<h1>BluePrint3D - Shared Product Options for Magento 2 📦</h1>

<p>
    <a href="#"><img src="https://img.shields.io/badge/Magento-2.4.x-orange.svg" alt="Magento Version" /></a>
    <a href="#"><img src="https://img.shields.io/badge/PHP-7.4%20|%208.1%20|%208.2-blue.svg" alt="PHP Version" /></a>
    <a href="#"><img src="https://img.shields.io/badge/License-Custom_EULA-lightgrey.svg" alt="License" /></a>
</p>

<p>A powerful, centralised custom options manager for Magento 2. Create complex product options once, group them together, and instantly assign them to hundreds of products. Update a price or add a new dropdown to the group, and watch it sync across your entire catalogue automatically.</p>

<hr />

<h2>🛑 The Problem</h2>
<p>Natively, Magento 2 forces you to recreate the exact same custom options (like "Gift Wrapping", "Custom Engraving", or "Extended Warranty") on every single product manually. If the price of your gift wrapping changes, you are forced to spend hours digging through individual product pages to update every single price modifier one by one.</p>

<h2>🛠️ The Solution</h2>
<p>This module introduces <strong>Shared Option Groups</strong>. You can now build out complex option sets in a dedicated Admin panel, complete with dropdowns, text fields, price modifiers, and image switchers. Once created, you simply edit any product and select the Shared Option Group from a clean, searchable multi-select field. The options instantly render on the storefront and inject perfectly into the cart and checkout.</p>

<h2>✨ Features</h2>
<ul>
    <li><strong>Centralised Management:</strong> Build and manage options in one place. Update an option's price, and it instantly reflects on all assigned products across your catalogue.</li>
    <li><strong>Advanced UI Assignment:</strong> Easily assign option groups to products using a sleek, searchable multi-select UI component in the product edit form.</li>
    <li><strong>Price &amp; Image Sync:</strong> Fully integrated frontend Knockout JS to handle dynamic price updates (<code>shared-options-price.js</code>) and gallery interactions (<code>option-image-switcher.js</code>) seamlessly.</li>
    <li><strong>Native Checkout Flow:</strong> Intercepts and hydrates shared options perfectly into standard quote and order items, ensuring no disruption to fulfilment.</li>
    <li><strong>Zero Core Overrides:</strong> Built using clean Magento 2 architecture (Plugins, ViewModels, and UI DataProviders) for maximum compatibility.</li>
</ul>

<hr />

<h2>📦 Installation</h2>

<p><strong>1. Install via Composer</strong></p>
<pre><code>composer require blueprint3d/module-shared-product-options</code></pre>

<p><strong>2. Enable the module</strong></p>
<pre><code>php bin/magento module:enable BluePrint3D_SharedProductOptions</code></pre>

<p><strong>3. Run the database upgrade</strong></p>
<pre><code>php bin/magento setup:upgrade</code></pre>

<p><strong>4. Compile and flush cache</strong></p>
<pre><code>php bin/magento setup:di:compile
php bin/magento cache:flush</code></pre>

<hr />

<h2>👨‍💻 Usage</h2>
<ol>
    <li>Navigate to the new <strong>Shared Option Groups</strong> section in your Magento Admin.</li>
    <li>Create a new Group (e.g., "Standard Add-ons") and add your desired custom options, values, and price modifiers.</li>
    <li>Go to <strong>Catalog &gt; Products</strong> and edit any product.</li>
    <li>Expand the new <strong>Blueprint3D Shared Options</strong> fieldset.</li>
    <li>Check the boxes for the option groups you want to assign and hit <strong>Save</strong>!</li>
</ol>

<hr />

<h2>📜 License</h2>
<p><strong>Copyright &copy; 2026 BluePrint3D Ltd. All rights reserved.</strong></p>

<p>This software is provided free of charge for personal or commercial use. However, the resale, redistribution, or sublicensing of this source code, modified or unmodified, for direct financial gain is strictly prohibited.</p>

<p>Please see the <code>LICENSE.txt</code> file for full terms and conditions.</p>

<p>
    <strong>Owned by:</strong> BluePrint3D Ltd (Company Registration Number: 13473806)<br />
    <strong>Email:</strong> <a href="mailto:support@blueprint3d.co.uk">support@blueprint3d.co.uk</a>
</p>
