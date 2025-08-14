#!/bin/bash

DOMAIN_NAME="nesadocs.in"

EMAIL_ENABLED=true

RELEASE=true

OBFUSCATED_FILE_NAMES=(
	Routes.php
	Auth.php
)

OBFUSCATED_FILE_PATHS=(
	app/Config/
	app/Filters/
)

TOTAL_FILES="${#OBFUSCATED_FILE_NAMES[@]}"

get_obfuscated_json() {
	json="{ "

	for ((i = 0; i < $TOTAL_FILES; i++)); do
		json=$json"\"${OBFUSCATED_FILE_NAMES[i]}\": \"${OBFUSCATED_FILE_PATHS[i]}\","
	done

	json=${json%,}
	json=$json" }"

	echo $json
}

customize_env_file() {
	if [[ "$DOMAIN_NAME" != "localhost" ]]; then
		echo -e "\t- Domain name updated to $DOMAIN_NAME."
		sed -i "s/localhost/$DOMAIN_NAME/g" ./build/.env
	fi

	if [ "$EMAIL_ENABLED" = true ]; then
		echo -e "\t- Email functionality enabled."
		sed "s/EMAIL_ENABLED = false/EMAIL_ENABLED = true/g" ./build/.env
	fi
}

images_cleanup() {
	dangling_images=$(docker images -f "dangling=true" -q)
	if [ ! -z "$dangling_images" ]; then
		docker rmi -f "$dangling_images"
	else
		echo "No Dangling images found for cleanup"
	fi

	echo "Docker Images cleanup Done"
}

docker_commands() {
	docker stack rm docsgo

	images_cleanup

	echo -e "\t- Initiating build."
	docker build -f Dockerfile -t docsgo/web .
	docker build -f Dockerfile-db -t docsgo/mysql .

	# echo -e "\t- Deploying build."
	# docker stack deploy -c docker-compose.yml docsgo

	if [ "$RELEASE" = true ]; then
		rm -rf release
		mkdir -p release

		echo -e "\t- Exporting Image."
		docker save docsgo/web | gzip >./release/docsgo_web.tar.gz
		docker save docsgo/mysql | gzip >./release/docsgo_db.tar.gz

		cp docker-compose-release.yml release/docker-compose.yml
		cp ./configurations/README.md release

		if [[ "$DOMAIN_NAME" != "localhost" ]]; then
			sed -i "s/localhost/$DOMAIN_NAME/g" ./release/README.md
		fi
	fi

}

echo "1. Creating build directory and copying source code."
rm -rf build
mkdir build
cp -r ../app ../public ../vendor ../writable ./configurations/.env build

echo "2. Customizing env file."
customize_env_file

echo "3. Obfuscating files - ${OBFUSCATED_FILE_NAMES[@]}"
json=$(get_obfuscated_json)
php ./obfuscator/obfuscate.php "${json}"

echo "4. Copying obfuscated files -"
for ((i = 0; i < $TOTAL_FILES; ++i)); do
	cmd="cp -rf ./obfuscator/output/${OBFUSCATED_FILE_NAMES[i]} ./build/${OBFUSCATED_FILE_PATHS[i]}${OBFUSCATED_FILE_NAMES[i]}"
	eval "$cmd"
done

echo "5. Copying license file"
cp -r ./configurations/License/license.key ./build/app/License/
cp -r ./configurations/License/adobe/pdfservices-api-credentials.json ./build/public/pdf-to-docx-converter
cp -r ./configurations/License/adobe/private.key ./build/public/pdf-to-docx-converter

echo "6. Executing Docker commands"
docker_commands
