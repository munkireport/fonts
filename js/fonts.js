// Fonts module functions

/**
 * Type enabled filter
 */
var type_enabled_filter = function(colNumber, d) {
    if (d.search.value.match(/^type_enabled_yes$/)) {
        d.columns[colNumber].search.value = '= 1';
        d.search.value = '';
    }
    
    if (d.search.value.match(/^type_enabled_no$/)) {
        d.columns[colNumber].search.value = '= 0';
        d.search.value = '';
    }
}

/**
 * Valid filter
 */
var valid_filter = function(colNumber, d) {
    if (d.search.value.match(/^valid_yes$/)) {
        d.columns[colNumber].search.value = '= 1';
        d.search.value = '';
    }
    
    if (d.search.value.match(/^valid_no$/)) {
        d.columns[colNumber].search.value = '= 0';
        d.search.value = '';
    }
}

/**
 * Duplicate filter
 */
var duplicate_filter = function(colNumber, d) {
    if (d.search.value.match(/^duplicate_yes$/)) {
        d.columns[colNumber].search.value = '= 1';
        d.search.value = '';
    }
    
    if (d.search.value.match(/^duplicate_no$/)) {
        d.columns[colNumber].search.value = '= 0';
        d.search.value = '';
    }
}

/**
 * Copy protected filter
 */
var copy_protected_filter = function(colNumber, d) {
    if (d.search.value.match(/^copy_protected_yes$/)) {
        d.columns[colNumber].search.value = '= 1';
        d.search.value = '';
    }
    
    if (d.search.value.match(/^copy_protected_no$/)) {
        d.columns[colNumber].search.value = '= 0';
        d.search.value = '';
    }
}

/**
 * Vendor filter  
 */
var vendor_filter = function(colNumber, d) {
    // Only activate for specific vendor search patterns, not general searches
    if (d.search.value.match(/^vendor:/)) {
        // Remove the vendor: prefix and use the rest as search value
        var vendorName = d.search.value.replace(/^vendor:/, '');
        d.columns[colNumber].search.value = '= ' + vendorName;
        d.search.value = '';
    }
}

/**
 * Type filter
 */
var type_filter = function(colNumber, d) {
    // Only activate for specific type search patterns, not general searches  
    if (d.search.value.match(/^type:/)) {
        // Remove the type: prefix and use the rest as search value
        var typeName = d.search.value.replace(/^type:/, '');
        d.columns[colNumber].search.value = '= ' + typeName;
        d.search.value = '';
    }
}

/**
 * Type name filter
 */
var type_name_filter = function(colNumber, d) {
    // Only activate for specific type name search patterns, not general searches
    if (d.search.value.match(/^typename:/)) {
        // Remove the typename: prefix and use the rest as search value
        var typeName = d.search.value.replace(/^typename:/, '');
        d.columns[colNumber].search.value = '= ' + typeName;
        d.search.value = '';
    }
} 