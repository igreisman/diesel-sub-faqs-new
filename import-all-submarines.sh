#!/bin/bash
# Batch import submarines from add-*.php files

echo "🚢 Importing submarines from add-*.php files..."
echo ""

# Find all add-*.php files
ADD_FILES=$(ls add-*-submarine.php 2>/dev/null)

if [ -z "$ADD_FILES" ]; then
    echo "❌ No add-*-submarine.php files found!"
    exit 1
fi

# Count files
COUNT=$(echo "$ADD_FILES" | wc -l | tr -d ' ')
echo "Found $COUNT submarine files to import:"
echo "$ADD_FILES" | sed 's/^/  - /'
echo ""

read -p "Import all submarines? (yes/no): " -r
echo

if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    echo "Import cancelled."
    exit 0
fi

echo ""
SUCCESS=0
FAILED=0

# Run each PHP file
for file in $ADD_FILES; do
    echo -n "Importing $(basename $file .php)... "
    
    # Run the PHP script and capture output
    OUTPUT=$(php "$file" 2>&1)
    
    if [ $? -eq 0 ]; then
        echo "✅"
        SUCCESS=$((SUCCESS + 1))
    else
        echo "❌"
        echo "  Error: $OUTPUT"
        FAILED=$((FAILED + 1))
    fi
done

echo ""
echo "═══════════════════════════════"
echo "Import complete!"
echo "  ✅ Successful: $SUCCESS"
if [ $FAILED -gt 0 ]; then
    echo "  ❌ Failed: $FAILED"
fi
echo "═══════════════════════════════"

# Show final count
echo ""
echo "Current submarine count:"
docker-compose exec -T db mariadb -udieselsu_dbuser -pcodjuw-xojWo6-datqem dieselsu_faqs -e "SELECT COUNT(*) as total FROM lost_submarines;" 2>/dev/null | grep -v total | tail -1
