# Create a temporary directory with the plugin name
mkdir -p ../temp-zip-dir/handl-utm-grabber-v3

# Copy all plugin files to the temp directory
rsync -av --exclude="*.git*" --exclude="node_modules/*" --exclude="*.idea*" --exclude="*.DS_Store" --exclude="*package-lock.json*" --exclude="premiums/vendor/*" --exclude="admin/*" --exclude="*.vscode*" --exclude=".github/*" ./ ../temp-zip-dir/handl-utm-grabber-v3/

# Copy only the built admin assets
mkdir -p ../temp-zip-dir/handl-utm-grabber-v3/admin/build
rsync -av admin/build/ ../temp-zip-dir/handl-utm-grabber-v3/admin/build/

# Obfuscate PHP files in specific directories only
echo "Starting PHP file obfuscation..."
cd ../temp-zip-dir/handl-utm-grabber-v3

# Find and obfuscate PHP files in premiums, includes, and external directories
find ./premiums ./includes ./external -name "*.php" -type f 2>/dev/null | while read -r php_file; do
echo "Obfuscating: $php_file"

# Create a temporary obfuscated file
temp_obfuscated="${php_file}.obfuscated"

# Use the obfuscator script to obfuscate the file
if php scripts/obfuscate.php "$php_file" "$temp_obfuscated"; then
    # Replace original with obfuscated version
    mv "$temp_obfuscated" "$php_file"
    echo "Successfully obfuscated: $php_file"
else
    echo "Failed to obfuscate: $php_file"
    # Remove temp file if it exists
    [ -f "$temp_obfuscated" ] && rm "$temp_obfuscated"
fi
done

echo "PHP obfuscation completed"

# Remove the scripts directory as it's no longer needed
echo "Removing scripts directory..."
rm -rf scripts
echo "Scripts directory removed"