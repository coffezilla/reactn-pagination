/* eslint-disable */
/* eslint-disable  operator-linebreak */
// npm install @types/react @types/react-native
import { Text, View, Button, ScrollView, RefreshControl } from 'react-native';
import React, { useEffect, useRef, useState } from 'react';
import axios from 'axios';

interface IProps {
	//  local data. no need if is getting
	data: any[] | string;
	// change the data in the app to show results
	setData: React.Dispatch<React.SetStateAction<string[]>>;
	// current page
	currentPage?: number;
	// number of results per page
	perPage?: number;
	// scroll to the bottom to load next page
	autoLoad?: boolean;
	// get all the results with one call
	saveLocalJson?: boolean;
	// field of the object ( data.pathData, data.users, data.xxx)
	pathData?: string;
	// callback function after successfully change the page
	callbackChangePage?: Function;
	// content
	children: any | null;
	// get param page
	paramPage?: string;
	// get param per page
	paramPerpage?: string;
	// aditional new params
	params: string;
}

interface IRef {
	allContent: number;
	page: number;
	pages: number[];
	viewport: number;
	result: any[];
	dataTemp: any[];
	localData: any[];
}

type serverData = {
	status: number;
	dataRawArr: any[];
	totalResults: number;
};

const Pagination = ({
	data,
	setData,
	currentPage = 1,
	perPage = 10,
	callbackChangePage,
	autoLoad = false,
	saveLocalJson = false,
	pathData = undefined,
	children,
	paramPage = 'page',
	paramPerpage = 'perpage',
	params = '',
}: IProps) => {
	const refContentScroll: React.MutableRefObject<IRef> = useRef({
		allContent: 0,
		page: currentPage,
		result: [],
		pages: [],
		viewport: 0,
		dataTemp: [],
		localData: [],
	});

	// check if is a endpoint or object
	let dataJson: any[] = [];
	let newContent: any[] = [];
	let pagesList: any[] = [];
	let totalResults: number = 0;
	const [isLoadingServerScroll, setIsLoadingServerScroll] = useState(false);
	const [reloadContent, setReloadContent] = useState(false);
	const [refreshing, setRefreshing] = useState(false);

	const dataRetrieveFrom =
		typeof data === 'object'
			? 'LOCAL'
			: saveLocalJson
			? 'SERVER_LOCAL'
			: 'SERVER';

	// get data from local json
	const getDataFromJson = (json: any[], page: number = 1): any => {
		dataJson = json;

		refContentScroll.current = {
			...refContentScroll.current,
			localData: dataJson,
		};

		const totalResultsLength = dataJson.length;
		const indexCurrent = page * perPage - perPage;
		const indexCurrentFirst = indexCurrent;
		const indexLastResult = indexCurrent + perPage;
		const indexLast =
			indexLastResult > totalResultsLength
				? totalResultsLength
				: indexLastResult;

		const serverResponse = {
			status: 1,
			dataRawArr: dataJson.slice(indexCurrentFirst, indexLast),
			totalResults: totalResultsLength,
		};
		return serverResponse;
	};

	// get Json from or rest from the endpoint from the server
	const getFullJsonResults = async (page: number): Promise<serverData> => {
		// token
		let userAuthToken = null;
		// await readItemFromStorage().then((responseStorage) => {
		// 	userAuthToken = responseStorage.auth.token;
		// });

		let serverResponse: serverData = {
			status: 0,
			dataRawArr: [],
			totalResults: 0,
		};

		// getting data from server each page
		if (dataRetrieveFrom === 'SERVER') {
			await axios({
				url: `${data}?${paramPage}=${page}&${paramPerpage}=${perPage}&${params}`,
				method: 'get',
				headers: { Authorization: `Bearer ${userAuthToken}` },
			})
				.then((responseData) => {
					dataJson = pathData ? responseData.data[pathData] : responseData.data;
					totalResults = responseData.data.totalRows;
					serverResponse = {
						status: 1,
						dataRawArr: pathData
							? responseData.data[pathData]
							: responseData.data,
						totalResults: responseData.data.totalRows,
					};
				})
				.catch((errorData) => {
					console.log('ENDPOINT NOT FOUND');
				});
		}

		// gettin data from server only the first time
		// after that, the json is treat like local json
		if (dataRetrieveFrom === 'SERVER_LOCAL') {
			if (refContentScroll.current.localData.length === 0) {
				await axios({
					url: `${data}?${params}`,
					method: 'get',
					headers: { Authorization: `Bearer ${userAuthToken}` },
				})
					.then((responseData) => {
						dataJson = pathData
							? responseData.data[pathData]
							: responseData.data;
						serverResponse = getDataFromJson(dataJson, page);
					})
					.catch((errorData) => {
						console.log('ENDPOINT NOT FOUND', errorData);
					});
			} else {
				serverResponse = getDataFromJson(
					refContentScroll.current.localData,
					page
				);
			}
		}

		// data is request from the local json passed
		if (dataRetrieveFrom === 'LOCAL') {
			if (typeof data === 'object') {
				dataJson = data;
			}
			serverResponse = getDataFromJson(dataJson, page);
		}

		return serverResponse;
	};

	const getResultPage = async (page: number = currentPage) => {
		setIsLoadingServerScroll(true);
		await getFullJsonResults(page).then((responseServerJson) => {
			if (responseServerJson.status === 1) {
				newContent = responseServerJson.dataRawArr;
				totalResults = responseServerJson.totalResults;

				// creating pages to front end
				const totalPages = Math.ceil(totalResults / perPage);
				pagesList = [];
				for (let i = 1; i <= totalPages; i += 1) {
					pagesList.push(i);
				}

				if (autoLoad) {
					refContentScroll.current = {
						...refContentScroll.current,
						page,
						result: [...refContentScroll.current.result, ...newContent],
						pages: pagesList,
					};
				} else {
					refContentScroll.current = {
						...refContentScroll.current,
						page,
						result: newContent,
						pages: pagesList,
					};
				}

				// update useData from the main page
				setData(refContentScroll.current.result);

				// return callback function with current page
				if (callbackChangePage) {
					callbackChangePage(page);
				}
			} else {
				console.log('COULD NOT GET DATA FROM THIS OBJECT OR THIS ENDPOINT');
			}

			setTimeout(() => {
				setIsLoadingServerScroll(false);
			}, 1000);

			setRefreshing(false);
		});
	};

	// click next page
	const handleNewPage = (page: number): void => {
		getResultPage(page);
	};

	// click prev page
	const handlePrevPage = (): void => {
		const pageGoesTo =
			refContentScroll.current.page === 1
				? 1
				: refContentScroll.current.page - 1;
		getResultPage(pageGoesTo);
	};

	// click number page
	const handleNextPage = (): void => {
		const pageGoesTo =
			refContentScroll.current.page === refContentScroll.current.pages.length
				? refContentScroll.current.page
				: refContentScroll.current.page + 1;
		getResultPage(pageGoesTo);
	};

	const isCloseToBottom = ({
		layoutMeasurement,
		contentOffset,
		contentSize,
	}) => {
		return (
			layoutMeasurement.height + contentOffset.y >= contentSize.height - 20
		);
	};

	const isCloseToTop = ({ layoutMeasurement, contentOffset, contentSize }) => {
		return contentOffset.y == 0;
	};

	//
	const onRefreshRanking = () => {
		refContentScroll.current = {
			allContent: 0,
			page: 1,
			result: [],
			pages: [],
			viewport: 0,
			dataTemp: [],
			localData: [],
		};

		setData(null);
		setRefreshing(true);
		getResultPage();
	};

	useEffect(() => {
		onRefreshRanking();
	}, [params]);

	return (
		<>
			{/* <pre>{JSON.stringify(refContentScroll, null, 1)}</pre> */}
			{/* <pre>{JSON.stringify(localData, null, 1)}</pre> */}

			<ScrollView
				refreshControl={
					<RefreshControl
						refreshing={refreshing}
						onRefresh={onRefreshRanking}
					/>
				}
				onScroll={({ nativeEvent }) => {
					if (autoLoad) {
						if (isCloseToTop(nativeEvent)) {
						}
						if (isCloseToBottom(nativeEvent)) {
							if (!isLoadingServerScroll) {
								const nextPage = refContentScroll.current.page + 1;
								getResultPage(nextPage);
							}
						}
					}
				}}
			>
				{children}
				{autoLoad ? (
					isLoadingServerScroll && (
						<View
							style={{
								backgroundColor: 'white',
								width: '100%',
								height: 200,
							}}
						>
							<Text
								style={{
									color: 'gray',
									fontSize: 20,
								}}
							/>
						</View>
					)
				) : (
					<>
						<Text>Pagination:</Text>

						{refContentScroll.current.page === 1 ? (
							<Text>Prev</Text>
						) : (
							<Button title='Prev' onPress={handlePrevPage} />
						)}

						{refContentScroll.current.pages.map(
							(page: number, index: number) => {
								return (
									<View key={page}>
										{page === refContentScroll.current.page ? (
											<Text>
												{page} - {index}
											</Text>
										) : (
											<Button
												title={`${page} - ${index}`}
												onPress={() => handleNewPage(page)}
											/>
										)}
									</View>
								);
							}
						)}

						{refContentScroll.current.page >=
						refContentScroll.current.pages.length ? (
							<Text>Next</Text>
						) : (
							<Button title='Next' onPress={handleNextPage} />
						)}
					</>
				)}
			</ScrollView>
		</>
	);
};

export default Pagination;
